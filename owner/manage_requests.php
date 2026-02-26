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
                $company_id = get_current_company_id();

                if (empty($target_user_id) || empty($shift_date) || empty($start_time) || empty($end_time)) {
                    throw new Exception('全ての項目を入力してください。');
                }

                // ユーザーが自社に所属しているかチェック
                $stmt_usr = $pdo->prepare("SELECT COUNT(*) FROM users WHERE user_id = ? AND company_id = ?");
                $stmt_usr->execute([$target_user_id, $company_id]);
                if ($stmt_usr->fetchColumn() == 0) {
                    throw new Exception('不正なユーザーIDです。');
                }

                // 定休日チェック (company_id考慮)
                $stmt_holiday = $pdo->prepare("SELECT COUNT(*) FROM holidays WHERE holiday_date = ? AND company_id = ?");
                $stmt_holiday->execute([$shift_date, $company_id]);
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
                header('Location: ' . BASE_PATH . '/owner/manage_requests.php?msg=' . urlencode($msg));
                exit;

            } else {
                // 既存のアクション (approve, reject, cancel, update)
                $company_id = get_current_company_id();
                $stmt = $pdo->prepare('SELECT r.user_id, r.shift_date, r.start_time, r.end_time, u.email, u.username FROM shifts_requested r JOIN users u ON r.user_id = u.user_id WHERE r.request_id = ? AND u.company_id = ? FOR UPDATE');
                $stmt->execute([$request_id, $company_id]);
                $req = $stmt->fetch();

                if (!$req) {
                    throw new Exception('該当リクエストが見つかりません。');
                }

                if ($action === 'approve') {
                    // 定休日チェック (company_id考慮)
                    $stmt_holiday = $pdo->prepare("SELECT COUNT(*) FROM holidays WHERE holiday_date = ? AND company_id = ?");
                    $stmt_holiday->execute([$req['shift_date'], $company_id]);
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
                        if (send_mail($req['email'], $subject, $body)) {
                            launch_background_process(__DIR__ . '/../process_mail.php');
                        }
                    }

                    $msg = '承認して確定シフトに追加しました。';

                } elseif ($action === 'reject') {
                    $up = $pdo->prepare('UPDATE shifts_requested SET request_status = "rejected" WHERE request_id = ?');
                    $up->execute([$request_id]);
                    
                    // メール通知
                    if (!empty($req['email'])) {
                        $subject = "【シフト却下】" . $req['shift_date'];
                        $body = "{$req['username']} さん\n\n申請された以下のシフト希望は却下されました。\n\n日付: {$req['shift_date']}\n時間: " . substr($req['start_time'], 0, 5) . " 〜 " . substr($req['end_time'], 0, 5) . "\n\n詳細は店長までお問い合わせください。";
                        if (send_mail($req['email'], $subject, $body)) {
                            launch_background_process(__DIR__ . '/../process_mail.php');
                        }
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
                header('Location: ' . BASE_PATH . '/owner/manage_requests.php?msg=' . urlencode($msg));
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
$company_id = get_current_company_id();
$stmt_users = $pdo->prepare("SELECT user_id, username FROM users WHERE user_type = 'part-time' AND is_deleted = 0 AND company_id = ? ORDER BY username");
$stmt_users->execute([$company_id]);
$users = $stmt_users->fetchAll();

// ============================================
// カレンダー用データ準備
// ============================================

// 表示対象の年月を取得（デフォルトは現在年月）
$year = intval($_GET['y'] ?? date('Y'));
$month = intval($_GET['m'] ?? date('n'));
if ($month < 1) { $month = 1; }
if ($month > 12) { $month = 12; }

// 月初と月末の日付を計算
$startOfMonth = date('Y-m-01', strtotime("{$year}-{$month}-01"));
$endOfMonth = date('Y-m-t', strtotime($startOfMonth));

// 全リクエスト取得（この月のもの）
$sql = 'SELECT r.request_id, r.user_id, r.shift_date, r.start_time, r.end_time, r.request_status, r.submitted_at, u.username
        FROM shifts_requested r JOIN users u ON r.user_id = u.user_id 
        WHERE u.company_id = ? AND r.shift_date BETWEEN ? AND ?
        ORDER BY r.shift_date, r.start_time';
$stmt = $pdo->prepare($sql);
$stmt->execute([$company_id, $startOfMonth, $endOfMonth]);
$requests = $stmt->fetchAll();

// 日付ごとの配列に整理
$requests_by_date = [];
$count_by_date = []; // 日付ごとの申請件数
$status_by_date = []; // 日付ごとのステータスの集計

foreach ($requests as $r) {
    $d = $r['shift_date'];
    if (!isset($requests_by_date[$d])) {
        $requests_by_date[$d] = [];
        $count_by_date[$d] = 0;
        $status_by_date[$d] = ['pending' => 0, 'approved' => 0, 'rejected' => 0];
    }
    $requests_by_date[$d][] = $r;
    $count_by_date[$d]++;
    $status = $r['request_status'];
    if (isset($status_by_date[$d][$status])) {
        $status_by_date[$d][$status]++;
    }
}

// 定休日の取得
$holidays = get_company_holidays($pdo, $company_id, $startOfMonth, $endOfMonth);

// 前月・次月
$prev = date('Y-n', strtotime($startOfMonth.' -1 month'));
$next = date('Y-n', strtotime($startOfMonth.' +1 month'));
list($py, $pm) = explode('-', $prev);
list($ny, $nm) = explode('-', $next);

// ステータス表示ラベル（日本語）
function status_label($s){
  $map = [
    'pending' => '保留',
    'approved' => '承認済み',
    'rejected' => '却下',
  ];
  return $map[$s] ?? htmlspecialchars($s);
}

render_header('希望シフト一覧（オーナー）', true, ['dashboard.css', 'calendar.css']);
?>
<div class="page-container">
  <div class="page-header">
    <h1 class="page-title"><i class="bi bi-calendar-check me-2"></i>希望シフト一覧</h1>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
      <i class="bi bi-plus-lg me-1"></i>新規シフト登録
    </button>
  </div>

  <?php if(!empty($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="bi bi-check-circle me-2"></i><?php echo htmlspecialchars($_GET['msg']); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>
  <?php if(!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- 月ナビゲーション -->
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h1 class="h4 mb-0"><?php echo htmlspecialchars("{$year}年{$month}月の希望シフト"); ?></h1>
    <div class="d-flex gap-2 flex-shrink-0">
      <a class="btn btn-sm btn-outline-primary" href="<?php echo BASE_PATH; ?>/owner/manage_requests.php?y=<?php echo htmlspecialchars($py); ?>&m=<?php echo htmlspecialchars($pm); ?>">&lt; 前月</a>
      <a class="btn btn-sm btn-outline-primary" href="<?php echo BASE_PATH; ?>/owner/manage_requests.php?y=<?php echo htmlspecialchars($ny); ?>&m=<?php echo htmlspecialchars($nm); ?>">次月 &gt;</a>
    </div>
  </div>

  <!-- フィルタータブ -->
  <div class="card shadow-sm mb-4">
    <div class="card-body p-3">
      <div class="d-flex gap-2 align-items-center">
        <span class="text-muted me-2 flex-shrink-0"><i class="bi bi-funnel me-1"></i>フィルター:</span>
        <div class="btn-group flex-grow-1" role="group">
          <button class="btn btn-outline-secondary filter-btn active" data-filter="all" onclick="setFilter('all', this)">
            <i class="bi bi-list-ul me-1"></i>すべて
          </button>
          <button class="btn btn-outline-secondary filter-btn" data-filter="pending" onclick="setFilter('pending', this)">
            <i class="bi bi-clock-history me-1"></i>保留
          </button>
          <button class="btn btn-outline-secondary filter-btn" data-filter="approved" onclick="setFilter('approved', this)">
            <i class="bi bi-check-circle me-1"></i>承認済
          </button>
          <button class="btn btn-outline-secondary filter-btn" data-filter="rejected" onclick="setFilter('rejected', this)">
            <i class="bi bi-x-circle me-1"></i>却下
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- ============================================ -->
  <!-- PC View: Calendar + Detail Panel -->
  <!-- ============================================ -->
  <div class="d-none d-md-block">
    <div class="row">
      <!-- Left Column: Calendar -->
      <div class="col-md-9">
        <table class="table table-bordered calendar-table h-100" id="pcCalendarTable">
          <thead class="table-light">
            <tr>
              <th class="text-danger">日</th>
              <th>月</th>
              <th>火</th>
              <th>水</th>
              <th>木</th>
              <th>金</th>
              <th class="text-primary">土</th>
            </tr>
          </thead>
          <tbody>
          <?php
          // カレンダー生成
          $firstWeekday = date('w', strtotime($startOfMonth)); // 0 (Sun) - 6 (Sat)
          $daysInMonth = date('t', strtotime($startOfMonth));
          
          $cells = [];
          for ($i = 0; $i < $firstWeekday; $i++) { $cells[] = null; }
          for ($d = 1; $d <= $daysInMonth; $d++) {
              $cells[] = date('Y-m-d', strtotime("{$startOfMonth} +".($d-1).' days'));
          }
          while (count($cells) % 7 !== 0) { $cells[] = null; }

          $rowsCount = intdiv(count($cells), 7);
          for ($r = 0; $r < $rowsCount; $r++) {
              echo "<tr>";
              for ($c = 0; $c < 7; $c++) {
                  $idx = $r * 7 + $c;
                  $date = $cells[$idx];
                  
                  $is_holiday = ($date !== null && in_array($date, $holidays));
                  $bg_class = $is_holiday ? 'bg-light text-muted' : '';
                  $cursor_class = $date ? 'cursor-pointer' : '';

                  // 今日の日付判定
                  if ($date === date('Y-m-d')) {
                      $bg_class .= ' bg-info bg-opacity-10';
                  }
                  
                  echo '<td class="calendar-cell ' . $bg_class . ' ' . $cursor_class . '" ';
                  if ($date) {
                      echo 'onclick="selectDatePC(this, \'' . $date . '\')" id="pc-cell-'.$date.'"';
                  }
                  echo '>';
                  
                  if ($date === null) {
                      echo '&nbsp;';
                  } else {
                      $dayNum = intval(substr($date,8,2));
                      echo '<div class="fw-bold mb-1">'.$dayNum.'</div>';
                      
                      if ($is_holiday) {
                          echo '<div class="badge bg-secondary mb-1" style="font-size:0.65rem;">定休日</div>';
                      }
                      
                      if (!empty($requests_by_date[$date])) {
                          $cnt = $count_by_date[$date];
                          $st = $status_by_date[$date];
                          echo '<div class="text-primary small fw-bold"><i class="bi bi-file-earmark-text"></i> ' . $cnt . '件</div>';
                          // ステータスドット
                          echo '<div class="d-flex gap-1 mt-1">';
                          if ($st['pending'] > 0) echo '<span class="request-dot dot-pending" title="保留 '.$st['pending'].'件"></span>';
                          if ($st['approved'] > 0) echo '<span class="request-dot dot-approved" title="承認 '.$st['approved'].'件"></span>';
                          if ($st['rejected'] > 0) echo '<span class="request-dot dot-rejected" title="却下 '.$st['rejected'].'件"></span>';
                          echo '</div>';
                      }
                  }
                  echo '</td>';
              }
              echo "</tr>";
          }
          ?>
          </tbody>
        </table>
      </div>

      <!-- Right Column: Detail Panel -->
      <div class="col-md-3">
        <div class="card shadow-sm sticky-top" style="top: 20px;">
          <div class="card-header bg-primary text-white">
            <i class="bi bi-calendar-event me-2"></i><span id="detailTitle">日付を選択</span>
          </div>
          <div class="card-body p-0" id="detailContent" style="min-height: 300px; max-height: 80vh; overflow-y: auto;">
            <p class="text-muted text-center py-5 px-3">カレンダーの日付をクリックすると<br>申請の詳細が表示されます。</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ============================================ -->
  <!-- Mobile View: Grid Calendar + Detail Area -->
  <!-- ============================================ -->
  <div class="d-md-none">
    <!-- Weekday Header -->
    <div class="d-flex text-center mb-2 fw-bold text-muted small">
      <div style="flex:1; color: var(--danger-color);">日</div>
      <div style="flex:1">月</div>
      <div style="flex:1">火</div>
      <div style="flex:1">水</div>
      <div style="flex:1">木</div>
      <div style="flex:1">金</div>
      <div style="flex:1; color: var(--primary-color);">土</div>
    </div>

    <div class="mobile-calendar-grid">
      <?php
      // Empty cells for start padding
      for ($i = 0; $i < $firstWeekday; $i++) { 
          echo '<div class="mobile-grid-cell empty"></div>';
      }

      for ($d = 1; $d <= $daysInMonth; $d++) {
          $currentDate = date('Y-m-d', strtotime("{$startOfMonth} +".($d-1).' days'));

          $is_today = ($currentDate === date('Y-m-d'));
          $is_holiday = in_array($currentDate, $holidays);
          $dayOfWeek = date('w', strtotime($currentDate));
          
          $reqs = $requests_by_date[$currentDate] ?? [];
          $has_requests = !empty($reqs);
          
          $cellClass = 'mobile-grid-cell';
          if ($is_today) $cellClass .= ' today';
          $dowClass = 'dow-' . $dayOfWeek;
      ?>
      <div class="<?php echo $cellClass; ?> <?php echo $dowClass; ?>" 
           onclick="selectDateMobile(this, '<?php echo $currentDate; ?>')"
           id="cell-<?php echo $currentDate; ?>">
          
          <span class="mobile-grid-date"><?php echo intval(substr($currentDate, 8, 2)); ?></span>
          
          <div class="mobile-grid-dots">
              <?php if ($is_holiday): ?>
                  <div class="mobile-dot dot-holiday"></div>
              <?php endif; ?>
              
              <?php if ($has_requests): 
                  $st = $status_by_date[$currentDate];
                  $cnt = $count_by_date[$currentDate];
              ?>
                  <span class="mobile-request-count"><?php echo $cnt; ?></span>
              <?php endif; ?>
          </div>
      </div>
      <?php } ?>
    </div>

    <!-- Detail Area -->
    <div class="mobile-detail-area" id="mobileDetailArea">
      <div class="mobile-detail-header" id="mobileDetailTitle">日付を選択してください</div>
      <div class="mobile-detail-content" id="mobileDetailContent">
        カレンダーの日付をタップすると申請の詳細が表示されます。
      </div>
    </div>
  </div>
</div>

<!-- ============================================ -->
<!-- JavaScript: カレンダー詳細パネル制御 -->
<!-- ============================================ -->
<script>
// PHPからデータをJSONとして受け渡す
const requestsData = <?php echo json_encode($requests_by_date, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
const holidaysData = <?php echo json_encode($holidays, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
const basePath = <?php echo json_encode(BASE_PATH); ?>;
const csrfToken = <?php echo json_encode(generate_csrf_token()); ?>;

let currentFilter = 'all';

function setFilter(filter, btn) {
    currentFilter = filter;
    // ボタンのアクティブ状態を切り替え
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    
    // カレンダーセルの見た目を更新
    updateCalendarCells();
    
    // 現在選択中の日付があれば詳細パネルを再描画
    if (window._currentSelectedDate) {
        renderDetail(window._currentSelectedDate);
    }
}

function updateCalendarCells() {
    // PC: セルの件数表示を更新
    document.querySelectorAll('#pcCalendarTable .calendar-cell').forEach(cell => {
        if (!cell.id) return;
        const date = cell.id.replace('pc-cell-', '');
        const reqs = requestsData[date];
        if (!reqs) return;
        
        const filtered = filterRequests(reqs);
        const countEl = cell.querySelector('.text-primary.small');
        if (countEl) {
            countEl.innerHTML = '<i class="bi bi-file-earmark-text"></i> ' + filtered.length + '件';
            countEl.style.display = filtered.length > 0 ? '' : 'none';
        }
    });
}

function filterRequests(reqs) {
    if (currentFilter === 'all') return reqs;
    return reqs.filter(r => r.request_status === currentFilter);
}

function renderDetail(dateStr) {
    window._currentSelectedDate = dateStr;
    
    const dateObj = new Date(dateStr);
    const dayOfWeek = ['日', '月', '火', '水', '木', '金', '土'][dateObj.getDay()];
    const formattedDate = dateObj.getDate() + '日 (' + dayOfWeek + ')';
    
    let html = '';
    
    // 定休日チェック
    if (holidaysData.includes(dateStr)) {
        html += '<div class="alert alert-secondary mb-2 py-2 px-3"><i class="bi bi-shop me-2"></i>定休日です</div>';
    }
    
    const reqs = requestsData[dateStr];
    const filtered = reqs ? filterRequests(reqs) : [];
    
    if (filtered.length > 0) {
        html += '<div class="list-group list-group-flush">';
        filtered.forEach(r => {
            const statusConfig = {
                'pending': { badge: 'bg-warning text-dark', icon: 'bi-clock-history', label: '保留' },
                'approved': { badge: 'bg-success', icon: 'bi-check-circle-fill', label: '承認済み' },
                'rejected': { badge: 'bg-danger', icon: 'bi-x-circle-fill', label: '却下' }
            };
            const cfg = statusConfig[r.request_status] || { badge: 'bg-secondary', icon: 'bi-circle', label: r.request_status };
            
            const startTime = r.start_time ? r.start_time.substring(0, 5) : '';
            const endTime = r.end_time ? r.end_time.substring(0, 5) : '';
            const isOvernight = endTime <= startTime;
            
            html += '<div class="list-group-item px-3 py-3">';
            
            // ヘッダー: 名前 + ステータス
            html += '<div class="d-flex justify-content-between align-items-center mb-2">';
            html += '<div class="fw-bold"><i class="bi bi-person-circle me-1 text-primary"></i>' + escapeHtml(r.username) + '</div>';
            html += '<span class="badge ' + cfg.badge + ' d-inline-flex align-items-center gap-1"><i class="' + cfg.icon + '"></i>' + cfg.label + '</span>';
            html += '</div>';
            
            // 時間
            html += '<div class="mb-2 ms-3">';
            html += '<i class="bi bi-clock me-1 text-muted"></i>';
            html += '<strong>' + startTime + ' - ' + endTime + '</strong>';
            if (isOvernight) html += ' <span class="badge bg-secondary" style="font-size:0.65rem;">翌日</span>';
            html += '</div>';
            
            // 操作ボタン
            html += '<div class="d-flex gap-2 justify-content-end">';
            if (r.request_status === 'pending') {
                html += '<button class="btn btn-outline-danger btn-sm" onclick="showConfirm(\'reject\', ' + r.request_id + ')">却下</button>';
                html += '<button class="btn btn-success btn-sm" onclick="showConfirm(\'approve\', ' + r.request_id + ')">承認</button>';
            } else if (r.request_status === 'approved') {
                html += '<button class="btn btn-outline-warning btn-sm" onclick="showConfirm(\'cancel_approval\', ' + r.request_id + ')">取消</button>';
                html += '<button class="btn btn-outline-primary btn-sm" onclick="showEditModal(' + r.request_id + ', \'' + escapeHtml(r.shift_date) + '\', \'' + startTime + '\', \'' + endTime + '\')">修正</button>';
            } else {
                html += '<span class="text-muted small">操作不可</span>';
            }
            html += '</div>';
            
            html += '</div>';
        });
        html += '</div>';
    } else {
        if (!holidaysData.includes(dateStr)) {
            const filterLabel = currentFilter === 'all' ? '' : '（フィルター: ' + getFilterLabel(currentFilter) + '）';
            html += '<div class="text-center text-muted py-4">申請はありません' + filterLabel + '</div>';
        }
    }
    
    return { title: formattedDate, html: html };
}

function getFilterLabel(f) {
    const map = { 'pending': '保留', 'approved': '承認済み', 'rejected': '却下', 'all': 'すべて' };
    return map[f] || f;
}

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

// PC: 日付選択
function selectDatePC(element, dateStr) {
    document.querySelectorAll('#pcCalendarTable td').forEach(el => el.classList.remove('calendar-selected'));
    element.classList.add('calendar-selected');
    
    const detail = renderDetail(dateStr);
    document.getElementById('detailTitle').textContent = detail.title;
    document.getElementById('detailContent').innerHTML = detail.html;
}

// Mobile: 日付選択
function selectDateMobile(element, dateStr) {
    document.querySelectorAll('.mobile-grid-cell').forEach(el => el.classList.remove('active'));
    element.classList.add('active');
    
    const detail = renderDetail(dateStr);
    document.getElementById('mobileDetailTitle').textContent = detail.title;
    document.getElementById('mobileDetailContent').innerHTML = detail.html;
}

// 確認モーダル処理
function showConfirm(action, requestId) {
    const modalTitle = document.querySelector('#confirmModal .modal-title');
    const modalBody = document.querySelector('#confirmModal .modal-body p');
    const confirmBtn = document.getElementById('confirmBtn');
    
    if (action === 'approve') {
        modalTitle.textContent = '承認の確認';
        modalBody.textContent = 'この希望シフトを承認して確定シフトに追加しますか？';
        confirmBtn.className = 'btn btn-success';
        confirmBtn.textContent = '承認する';
    } else if (action === 'reject') {
        modalTitle.textContent = '却下の確認';
        modalBody.textContent = 'この希望シフトを却下しますか？';
        confirmBtn.className = 'btn btn-danger';
        confirmBtn.textContent = '却下する';
    } else if (action === 'cancel_approval') {
        modalTitle.textContent = '承認取り消しの確認';
        modalBody.textContent = '承認を取り消し、保留状態に戻しますか？\n確定シフトからも削除されます。';
        confirmBtn.className = 'btn btn-warning';
        confirmBtn.textContent = '取り消す';
    }
    
    confirmBtn.onclick = function() {
        submitAction(action, requestId);
    };
    
    const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
    modal.show();
}

function submitAction(action, requestId) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'manage_requests.php';
    
    const fields = { csrf_token: csrfToken, action: action, request_id: requestId };
    for (const [key, val] of Object.entries(fields)) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = val;
        form.appendChild(input);
    }
    
    document.body.appendChild(form);
    form.submit();
}

// 修正モーダル処理
function showEditModal(requestId, date, startTime, endTime) {
    document.getElementById('edit_request_id').value = requestId;
    document.getElementById('edit_shift_date').value = date;
    document.getElementById('edit_start_time').value = startTime;
    document.getElementById('edit_end_time').value = endTime;
    
    const modal = new bootstrap.Modal(document.getElementById('editModal'));
    modal.show();
}

// 初期表示時、今日の日付があれば選択
document.addEventListener('DOMContentLoaded', function() {
    const today = '<?php echo date("Y-m-d"); ?>';
    const startOfMonth = '<?php echo $startOfMonth; ?>';
    
    // PC
    const todayCellPC = document.getElementById('pc-cell-' + today);
    if (todayCellPC) {
        todayCellPC.click();
    } else {
        const firstDayCellPC = document.getElementById('pc-cell-' + startOfMonth);
        if (firstDayCellPC) firstDayCellPC.click();
    }
    
    // Mobile
    const todayCellMobile = document.getElementById('cell-' + today);
    if (todayCellMobile) {
        selectDateMobile(todayCellMobile, today);
    } else {
        const firstCell = document.querySelector('.mobile-grid-cell:not(.empty)');
        if (firstCell) {
            const cellId = firstCell.id;
            const cellDate = cellId.replace('cell-', '');
            selectDateMobile(firstCell, cellDate);
        }
    }
});
</script>

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
