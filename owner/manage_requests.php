<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../config.php';
// session_start(); // template.phpで開始するため削除
require_once __DIR__ . '/../template.php';

// オーナー専用ページ
if (empty($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'owner') {
    header('Location: ' . BASE_PATH . '/index.php');
    exit;
}

$pdo = getPDO();

// 処理: 承認 / 却下
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $request_id = intval($_POST['request_id'] ?? 0);

    // CSRFチェック
    // CSRFチェック
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'セッションが無効です。もう一度お試しください。';
    } elseif ($request_id > 0 && in_array($action, ['approve','reject','cancel_approval','update_shift'], true) || $action === 'add_shift') {
        try {
            $pdo->beginTransaction();

            if ($action === 'add_shift') {
                // 新規シフト登録
                if (!validate_csrf_token($_POST['csrf_token'] ?? '')) throw new Exception('セッションが無効です。');
                
                $target_user_id = $_POST['user_id'] ?? '';
                $shift_date = $_POST['shift_date'] ?? '';
                $start_time = $_POST['start_time'] ?? '';
                $end_time = $_POST['end_time'] ?? '';

                if (empty($target_user_id) || empty($shift_date) || empty($start_time) || empty($end_time)) {
                    throw new Exception('全ての項目を入力してください。');
                }

                // 定休日チェック
                $stmt_holiday = $pdo->prepare("SELECT COUNT(*) FROM holidays WHERE holiday_date = ?");
                $stmt_holiday->execute([$shift_date]);
                if ($stmt_holiday->fetchColumn() > 0) {
                    throw new Exception('指定された日付は定休日です。');
                }

                // 重複チェック
                $stmt_dup = $pdo->prepare("SELECT COUNT(*) FROM shifts_scheduled WHERE user_id = ? AND shift_date = ?");
                $stmt_dup->execute([$target_user_id, $shift_date]);
                if ($stmt_dup->fetchColumn() > 0) {
                    throw new Exception('そのユーザーは既にその日にシフトが入っています。');
                }

                // shifts_requested に approved として登録
                $ins_req = $pdo->prepare('INSERT INTO shifts_requested (user_id, shift_date, start_time, end_time, request_status, submitted_at) VALUES (?, ?, ?, ?, "approved", NOW())');
                $ins_req->execute([$target_user_id, $shift_date, $start_time, $end_time]);

                // shifts_scheduled に登録
                $ins_sch = $pdo->prepare('INSERT INTO shifts_scheduled (user_id, shift_date, start_time, end_time) VALUES (?, ?, ?, ?)');
                $ins_sch->execute([$target_user_id, $shift_date, $start_time, $end_time]);

                $msg = '新しいシフトを登録しました。';
                $pdo->commit();
                header('Location: ' . BASE_PATH . '/owner/manage_requests.php?msg=' . urlencode($msg) . '&filter=approved');
                exit;

            } else {
                // 既存のアクション (approve, reject, cancel, update)
                $stmt = $pdo->prepare('SELECT r.user_id, r.shift_date, r.start_time, r.end_time, u.email, u.username FROM shifts_requested r JOIN users u ON r.user_id = u.user_id WHERE r.request_id = ? FOR UPDATE');
                $stmt->execute([$request_id]);
                $req = $stmt->fetch();

                if (!$req) {
                    throw new Exception('該当リクエストが見つかりません。');
                }

                if ($action === 'approve') {
                    // ... (省略: 既存コードと同じ) ... 
                    // 上書き範囲外のため、このブロックは既存ロジックとマージされるように書き換えが必要だが、
                    // ここでは既存のif文構造を維持しつつ、add_shiftを別扱いにする。
                    // 実際には replace_file_content でこのブロック全体を置換すると既存の中身が消えるので、
                    // 既存ロジックを再記述する必要がある。
                    
                    // 定休日チェック
                    $stmt_holiday = $pdo->prepare("SELECT COUNT(*) FROM holidays WHERE holiday_date = ?");
                    $stmt_holiday->execute([$req['shift_date']]);
                    if ($stmt_holiday->fetchColumn() > 0) {
                        throw new Exception('指定された日付は定休日です。承認できません。');
                    }

                    // shifts_scheduled に追加
                    $ins = $pdo->prepare('INSERT INTO shifts_scheduled (user_id, shift_date, start_time, end_time) VALUES (?, ?, ?, ?)');
                    $ins->execute([$req['user_id'], $req['shift_date'], $req['start_time'], $req['end_time']]);
                    // リクエストを approved に
                    $up = $pdo->prepare('UPDATE shifts_requested SET request_status = "approved" WHERE request_id = ?');
                    $up->execute([$request_id]);
                    
                    // メール通知
                    if (!empty($req['email'])) {
                        $subject = "【シフト承認】" . $req['shift_date'];
                        $body = "{$req['username']} さん\n\n申請された以下のシフト希望が承認されました。\n\n日付: {$req['shift_date']}\n時間: " . substr($req['start_time'], 0, 5) . " 〜 " . substr($req['end_time'], 0, 5) . "\n\nご確認ください。";
                        send_mail($req['email'], $subject, $body);
                    }

                    $msg = '承認して確定シフトに追加しました。';

                } elseif ($action === 'reject') {
                    $up = $pdo->prepare('UPDATE shifts_requested SET request_status = "rejected" WHERE request_id = ?');
                    $up->execute([$request_id]);
                    
                    // メール通知
                    if (!empty($req['email'])) {
                        $subject = "【シフト却下】" . $req['shift_date'];
                        $body = "{$req['username']} さん\n\n申請された以下のシフト希望は却下されました。\n\n日付: {$req['shift_date']}\n時間: " . substr($req['start_time'], 0, 5) . " 〜 " . substr($req['end_time'], 0, 5) . "\n\n詳細は店長までお問い合わせください。";
                        send_mail($req['email'], $subject, $body);
                    }

                    $msg = '却下しました。';

                } elseif ($action === 'cancel_approval') {
                    // 確定シフトから削除
                    $del = $pdo->prepare('DELETE FROM shifts_scheduled WHERE user_id = ? AND shift_date = ?');
                    $del->execute([$req['user_id'], $req['shift_date']]);
                    
                    // pendingに戻す
                    $up = $pdo->prepare('UPDATE shifts_requested SET request_status = "pending" WHERE request_id = ?');
                    $up->execute([$request_id]);
                    
                    $msg = '承認を取り消し、保留に戻しました。';

                } elseif ($action === 'update_shift') {
                    $new_start = $_POST['start_time'] ?? '';
                    $new_end = $_POST['end_time'] ?? '';
                    
                    if (empty($new_start) || empty($new_end)) throw new Exception("開始・終了時刻は必須です。");

                    // Update Request Table
                    $upReq = $pdo->prepare('UPDATE shifts_requested SET start_time = ?, end_time = ? WHERE request_id = ?');
                    $upReq->execute([$new_start, $new_end, $request_id]);

                    // Update Schedule Table
                    $upSch = $pdo->prepare('UPDATE shifts_scheduled SET start_time = ?, end_time = ? WHERE user_id = ? AND shift_date = ?');
                    $upSch->execute([$new_start, $new_end, $req['user_id'], $req['shift_date']]);
                    
                    $msg = 'シフト時間を修正しました。';
                }
                
                $pdo->commit();
                header('Location: ' . BASE_PATH . '/owner/manage_requests.php?msg=' . urlencode($msg) . '&filter=' . ($action === 'update_shift' ? 'approved' : 'pending'));
                exit;
            }

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = $e->getMessage();
        }
    } else {
        $error = '無効なリクエストです。';
    }
}

// ユーザー一覧取得（新規登録用）
$stmt_users = $pdo->prepare("SELECT user_id, username FROM users WHERE user_type = 'part-time' AND is_deleted = 0 ORDER BY username");
$stmt_users->execute();
$users = $stmt_users->fetchAll();

// リクエスト一覧取得（オプション: フィルタ）
$filter = $_GET['filter'] ?? 'pending'; // pending/all/approved/rejected
$sql = 'SELECT r.request_id, r.user_id, r.shift_date, r.start_time, r.end_time, r.request_status, r.submitted_at, u.username
        FROM shifts_requested r JOIN users u ON r.user_id = u.user_id';
if ($filter === 'all') {
    $sql .= ' ORDER BY r.request_status, r.shift_date';
} else {
    $sql .= ' WHERE r.request_status = ? ORDER BY r.shift_date';
}

if ($filter === 'all') {
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
} else {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$filter]);
}
$requests = $stmt->fetchAll();

// ステータス表示ラベル（日本語）
function status_label($s){
  $map = [
    'pending' => '保留',
    'approved' => '承認済み',
    'rejected' => '却下',
  ];
  return $map[$s] ?? htmlspecialchars($s);
}

render_header('希望シフト一覧（オーナー）');
?>
<div class="row">
  <div class="col-md-12">
    <h1 class="h4 mb-3">希望シフト一覧</h1>
    <div class="mb-3 d-flex justify-content-between align-items-center">
      <div class="btn-group">
        <a class="btn btn-sm btn-outline-secondary filter-btn <?php echo $filter==='pending'?'active':''; ?>" href="<?php echo BASE_PATH; ?>/owner/manage_requests.php?filter=pending">保留</a>
        <a class="btn btn-sm btn-outline-secondary filter-btn <?php echo $filter==='all'?'active':''; ?>" href="<?php echo BASE_PATH; ?>/owner/manage_requests.php?filter=all">すべて</a>
        <a class="btn btn-sm btn-outline-secondary filter-btn <?php echo $filter==='approved'?'active':''; ?>" href="<?php echo BASE_PATH; ?>/owner/manage_requests.php?filter=approved">承認済み</a>
        <a class="btn btn-sm btn-outline-secondary filter-btn <?php echo $filter==='rejected'?'active':''; ?>" href="<?php echo BASE_PATH; ?>/owner/manage_requests.php?filter=rejected">却下</a>
      </div>
      
      <button type="button" class="btn btn-sm btn-primary text-nowrap" data-bs-toggle="modal" data-bs-target="#createModal">
        <i class="bi bi-plus-lg me-1"></i>新規<span class="d-none d-md-inline">シフト登録</span>
      </button>
    </div>

    <?php if(!empty($_GET['msg'])): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($_GET['msg']); ?></div>
    <?php endif; ?>
    <?php if(!empty($error)): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if(empty($requests)): ?>
      <div class="alert alert-info">該当する希望シフトはありません。</div>
    <?php else: ?>
      <!-- PC View: Table -->
      <div class="d-none d-md-block">
        <table class="table table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>ユーザー</th>
              <th>日付</th>
              <th>時間</th>
              <th>状態</th>
              <th>提出日時</th>
              <th class="text-end">操作</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach($requests as $r): ?>
            <tr>
              <td>
                <div class="fw-bold"><i class="bi bi-person-circle me-1"></i><?php echo htmlspecialchars($r['username']); ?></div>
              </td>
              <td><?php echo htmlspecialchars($r['shift_date']); ?></td>
              <td>
                <?php echo htmlspecialchars(substr($r['start_time'],0,5)); ?> - <?php echo htmlspecialchars(substr($r['end_time'],0,5)); ?>
                <?php if(strtotime($r['end_time']) <= strtotime($r['start_time'])) echo ' <span class="badge bg-secondary">翌日</span>'; ?>
              </td>
              <td>
                <?php 
                  $status = $r['request_status'];
                  $badgeClass = 'bg-secondary';
                  if($status === 'approved') $badgeClass = 'bg-success';
                  if($status === 'rejected') $badgeClass = 'bg-danger';
                  if($status === 'pending') $badgeClass = 'bg-warning text-dark';
                ?>
                <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars(status_label($status)); ?></span>
              </td>
              <td class="small text-muted"><?php echo htmlspecialchars($r['submitted_at']); ?></td>
              <td class="text-end">
                <?php if($r['request_status'] === 'pending'): ?>
                    <form method="post" action="manage_requests.php" style="display:inline" id="form-reject-<?php echo $r['request_id']; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo h(generate_csrf_token()); ?>">
                    <input type="hidden" name="request_id" value="<?php echo intval($r['request_id']); ?>">
                    <input type="hidden" name="action" value="reject">
                    <button type="button" class="btn btn-sm btn-outline-danger" 
                            data-bs-toggle="modal" 
                            data-bs-target="#confirmModal" 
                            data-action="reject" 
                            data-request-id="<?php echo $r['request_id']; ?>">却下</button>
                  </form>
                  <form method="post" action="manage_requests.php" style="display:inline" id="form-approve-<?php echo $r['request_id']; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo h(generate_csrf_token()); ?>">
                    <input type="hidden" name="request_id" value="<?php echo intval($r['request_id']); ?>">
                    <input type="hidden" name="action" value="approve">
                    <button type="button" class="btn btn-sm btn-success ms-1" 
                            data-bs-toggle="modal" 
                            data-bs-target="#confirmModal" 
                            data-action="approve" 
                            data-request-id="<?php echo $r['request_id']; ?>">承認</button>
                  </form>
                <?php elseif($r['request_status'] === 'approved'): ?>
                  <form method="post" action="manage_requests.php" style="display:inline" id="form-cancel-<?php echo $r['request_id']; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo h(generate_csrf_token()); ?>">
                    <input type="hidden" name="request_id" value="<?php echo intval($r['request_id']); ?>">
                    <input type="hidden" name="action" value="cancel_approval">
                    <button type="button" class="btn btn-sm btn-outline-warning" 
                            data-bs-toggle="modal" 
                            data-bs-target="#confirmModal" 
                            data-action="cancel_approval" 
                            data-request-id="<?php echo $r['request_id']; ?>">取消</button>
                  </form>
                  <button type="button" class="btn btn-sm btn-outline-primary btn-edit ms-1"
                          data-request-id="<?php echo $r['request_id']; ?>"
                          data-date="<?php echo $r['shift_date']; ?>"
                          data-start="<?php echo htmlspecialchars(substr($r['start_time'],0,5)); ?>"
                          data-end="<?php echo htmlspecialchars(substr($r['end_time'],0,5)); ?>">修正</button>
                <?php else: ?>
                  <span class="text-muted">-</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Mobile View: Cards -->
      <div class="d-block d-md-none">
        <?php foreach($requests as $r): ?>
          <div class="card mb-3 shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="fw-bold fs-5">
                    <i class="bi bi-person-fill me-1 text-primary"></i><?php echo htmlspecialchars($r['username']); ?>
                </div>
                <?php 
                  $status = $r['request_status'];
                  $badgeClass = 'bg-secondary';
                  if($status === 'approved') $badgeClass = 'bg-success';
                  if($status === 'rejected') $badgeClass = 'bg-danger';
                  if($status === 'pending') $badgeClass = 'bg-warning text-dark';
                ?>
                <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars(status_label($status)); ?></span>
              </div>
              
              <div class="mb-3">
                <div class="d-flex align-items-center mb-1">
                    <i class="bi bi-calendar-event me-2 text-muted"></i>
                    <span class="fw-bold"><?php echo htmlspecialchars($r['shift_date']); ?></span>
                </div>
                <div class="d-flex align-items-center">
                    <i class="bi bi-clock me-2 text-muted"></i>
                    <span>
                        <?php echo htmlspecialchars(substr($r['start_time'],0,5)); ?> - <?php echo htmlspecialchars(substr($r['end_time'],0,5)); ?>
                        <?php if(strtotime($r['end_time']) <= strtotime($r['start_time'])) echo ' <small class="text-danger">(翌日)</small>'; ?>
                    </span>
                </div>
              </div>

              <div class="d-flex justify-content-end gap-2 border-top pt-3 mt-2">
                <?php if($r['request_status'] === 'pending'): ?>
                    <!-- Mobile Reject Form -->
                    <form method="post" action="manage_requests.php" id="form-reject-m-<?php echo $r['request_id']; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo h(generate_csrf_token()); ?>">
                        <input type="hidden" name="request_id" value="<?php echo intval($r['request_id']); ?>">
                        <input type="hidden" name="action" value="reject">
                        <button type="button" class="btn btn-outline-danger" 
                                data-bs-toggle="modal" 
                                data-bs-target="#confirmModal" 
                                data-action="reject" 
                                data-request-id="<?php echo $r['request_id']; ?>"
                                onclick="document.getElementById('form-reject-<?php echo $r['request_id']; ?>') ? null : (window.mobileFormTarget = 'form-reject-m-<?php echo $r['request_id']; ?>')">却下</button>
                    </form>
                    <!-- Mobile Approve Form -->
                    <form method="post" action="manage_requests.php" id="form-approve-m-<?php echo $r['request_id']; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo h(generate_csrf_token()); ?>">
                        <input type="hidden" name="request_id" value="<?php echo intval($r['request_id']); ?>">
                        <input type="hidden" name="action" value="approve">
                        <button type="button" class="btn btn-success" 
                                data-bs-toggle="modal" 
                                data-bs-target="#confirmModal" 
                                data-action="approve" 
                                data-request-id="<?php echo $r['request_id']; ?>"
                                onclick="document.getElementById('form-approve-<?php echo $r['request_id']; ?>') ? null : (window.mobileFormTarget = 'form-approve-m-<?php echo $r['request_id']; ?>')">承認</button>
                    </form>
                <?php elseif($r['request_status'] === 'approved'): ?>
                     <!-- Mobile Cancel Form -->
                    <form method="post" action="manage_requests.php" id="form-cancel-m-<?php echo $r['request_id']; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo h(generate_csrf_token()); ?>">
                        <input type="hidden" name="request_id" value="<?php echo intval($r['request_id']); ?>">
                        <input type="hidden" name="action" value="cancel_approval">
                        <button type="button" class="btn btn-outline-warning" 
                                data-bs-toggle="modal" 
                                data-bs-target="#confirmModal" 
                                data-action="cancel_approval" 
                                data-request-id="<?php echo $r['request_id']; ?>"
                                onclick="document.getElementById('form-cancel-<?php echo $r['request_id']; ?>') ? null : (window.mobileFormTarget = 'form-cancel-m-<?php echo $r['request_id']; ?>')">取消</button>
                    </form>
                    <button type="button" class="btn btn-outline-primary btn-edit"
                            data-request-id="<?php echo $r['request_id']; ?>"
                            data-date="<?php echo $r['shift_date']; ?>"
                            data-start="<?php echo htmlspecialchars(substr($r['start_time'],0,5)); ?>"
                            data-end="<?php echo htmlspecialchars(substr($r['end_time'],0,5)); ?>">修正</button>
                <?php else: ?>
                    <span class="text-muted small">操作不可</span>
                <?php endif; ?>
              </div>
              <div class="text-end mt-1">
                 <small class="text-muted" style="font-size: 0.75rem;">提出: <?php echo htmlspecialchars($r['submitted_at']); ?></small>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmModalLabel">確認</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p id="confirmMessage">この操作を実行しますか？</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
        <button type="button" class="btn btn-primary" id="confirmBtn">実行</button>
      </div>
    </div>
  </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">新規シフト登録（代理対応など）</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form method="post" action="manage_requests.php" id="createForm">
            <input type="hidden" name="csrf_token" value="<?php echo h(generate_csrf_token()); ?>">
            <input type="hidden" name="action" value="add_shift">
            
            <div class="mb-3">
                <label class="form-label">アルバイトスタッフ</label>
                <select name="user_id" class="form-select" required>
                    <option value="">-- 選択してください --</option>
                    <?php foreach($users as $u): ?>
                        <option value="<?php echo $u['user_id']; ?>"><?php echo htmlspecialchars($u['username']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">日付</label>
                <input type="date" name="shift_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
            </div>
            
            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label">開始時間</label>
                    <input type="time" name="start_time" class="form-control" required>
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label">終了時間</label>
                    <input type="time" name="end_time" class="form-control" required>
                </div>
            </div>
            
            <div class="text-end">
                <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">キャンセル</button>
                <button type="submit" class="btn btn-primary">登録する</button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var confirmModal = document.getElementById('confirmModal');
    var confirmBtn = document.getElementById('confirmBtn');
    var targetFormId = null;

    // Edit Modal elements
    var editModal = new bootstrap.Modal(document.getElementById('editModal'));
    var editForm = document.getElementById('editForm');
    var editRequestId = document.getElementById('edit_request_id');
    var editDate = document.getElementById('edit_shift_date');
    var editStart = document.getElementById('edit_start_time');
    var editEnd = document.getElementById('edit_end_time');

    confirmModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var action = button.getAttribute('data-action');
        var requestId = button.getAttribute('data-request-id');
        var modalTitle = confirmModal.querySelector('.modal-title');
        var modalBody = confirmModal.querySelector('.modal-body p');
        
        // Mobile detection: Check if button is inside the mobile view container
        var isMobile = button.closest('.d-md-none') !== null;
        var suffix = isMobile ? '-m-' : '-'; 

        if (action === 'approve') {
            modalTitle.textContent = '承認の確認';
            modalBody.textContent = 'この希望シフトを承認して確定シフトに追加しますか？';
            confirmBtn.className = 'btn btn-success';
            confirmBtn.textContent = '承認する';
            targetFormId = 'form-approve' + suffix + requestId;
        } else if (action === 'reject') {
            modalTitle.textContent = '却下の確認';
            modalBody.textContent = 'この希望シフトを却下しますか？';
            confirmBtn.className = 'btn btn-danger';
            confirmBtn.textContent = '却下する';
            targetFormId = 'form-reject' + suffix + requestId;
        } else if (action === 'cancel_approval') {
            modalTitle.textContent = '承認取り消しの確認';
            modalBody.textContent = '承認を取り消し、保留状態に戻しますか？\n確定シフトからも削除されます。';
            confirmBtn.className = 'btn btn-warning';
            confirmBtn.textContent = '取り消す';
            targetFormId = 'form-cancel' + suffix + requestId;
        }
    });

    confirmBtn.addEventListener('click', function() {
        if (targetFormId) {
            var form = document.getElementById(targetFormId);
            if (form) {
                form.submit();
            } else {
                // Fallback: try the other ID style just in case
                var altId = targetFormId.includes('-m-') ? targetFormId.replace('-m-', '-') : targetFormId.replace('-', '-m-');
                var altForm = document.getElementById(altId);
                if(altForm) altForm.submit();
                else alert('Error: Form not found for ' + targetFormId);
            }
        }
    });

    // Handle Edit Button Click
    document.querySelectorAll('.btn-edit').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var reqId = this.getAttribute('data-request-id');
            var date = this.getAttribute('data-date');
            var start = this.getAttribute('data-start');
            var end = this.getAttribute('data-end');

            editRequestId.value = reqId;
            editDate.value = date;
            editStart.value = start;
            editEnd.value = end;
            
            editModal.show();
        });
    });
});
</script>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">シフト修正</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form method="post" action="manage_requests.php" id="editForm">
            <input type="hidden" name="csrf_token" value="<?php echo h(generate_csrf_token()); ?>">
            <input type="hidden" name="action" value="update_shift">
            <input type="hidden" name="request_id" id="edit_request_id">
            
            <div class="mb-3">
                <label class="form-label">日付</label>
                <input type="text" class="form-control" id="edit_shift_date" readonly disabled>
                <div class="form-text">日付の変更はできません。変更が必要な場合は却下して再提出を求めてください。</div>
            </div>
            
            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label">開始時間</label>
                    <input type="time" name="start_time" id="edit_start_time" class="form-control" required>
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label">終了時間</label>
                    <input type="time" name="end_time" id="edit_end_time" class="form-control" required>
                </div>
            </div>
            
            <div class="text-end">
                <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">キャンセル</button>
                <button type="submit" class="btn btn-primary">修正して保存</button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php render_footer(); ?>
