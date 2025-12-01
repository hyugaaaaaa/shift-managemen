<?php
require_once __DIR__ . '/../config.php';

session_start();

if (empty($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . '/index.php');
    exit;
}

$pdo = getPDO();
$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$target_month = $_GET['month'] ?? date('Y-m');

// オーナーは任意のユーザーを見れるが、パートは自分だけ
$target_user_id = $user_id;
if ($user_type === 'owner' && !empty($_GET['user_id'])) {
    $target_user_id = $_GET['user_id'];
}

// ユーザー情報取得
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$target_user_id]);
$user = $stmt->fetch();

if (!$user) die('User not found');

// 設定取得
$closing_day = (int)get_system_setting($pdo, 'closing_day', 15);

// 期間計算
$year = (int)substr($target_month, 0, 4);
$month = (int)substr($target_month, 5, 2);

$start_date_obj = new DateTime("$year-$month-$closing_day");
$start_date_obj->modify('-1 month');
$start_date_obj->modify('+1 day');
$start_date = $start_date_obj->format('Y-m-d');

$end_date_obj = new DateTime("$year-$month-$closing_day");
$end_date = $end_date_obj->format('Y-m-d');

// 支払日計算（翌月25日など）
$payment_day = (int)get_system_setting($pdo, 'payment_day', 25);
$payment_date_obj = new DateTime("$year-$month-$payment_day");
// 締め日が15日で支払いが25日なら同月払い？それとも翌月？
// 一般的には「当月25日」か「翌月25日」。ここでは「締め日の月の25日」とする（15日締め25日払い）
// もし締め日(15) > 支払日(10) なら翌月払いだが、今回は 15締め25払いなので同月。
if ($payment_day < $closing_day) {
    $payment_date_obj->modify('+1 month');
}
$payment_date = $payment_date_obj->format('Y年m月d日');


// シフト集計 (monthly_hours.php と同じロジック)
// 指定期間のシフトデータを取得
$stmt = $pdo->prepare('
    SELECT * FROM shifts_scheduled 
    WHERE user_id = ? AND shift_date BETWEEN ? AND ?
');
$stmt->execute([$target_user_id, $start_date, $end_date]);
$shifts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$days_worked = 0;
$normal_minutes = 0;
$night_minutes = 0;

foreach($shifts as $s) {
    $days_worked++;
    
    // 共通関数で時間計算
    $times = calculate_shift_minutes($s['shift_date'], $s['start_time'], $s['end_time']);
    
    $night_minutes += $times['night_minutes'];
    $normal_minutes += $times['normal_minutes'];
}

// 給与計算
$rate = (float)$user['hourly_rate'];
$transport = (float)$user['transportation_expense'];

// 共通関数で給与計算
$salary = calculate_salary_amount($normal_minutes, $night_minutes, $rate);
$pay_normal = $salary['pay_normal'];
$pay_night = $salary['pay_night'];
$pay_transport = $days_worked * $transport;
$total_pay = $salary['subtotal'] + $pay_transport;

// ビューの読み込み
require_once __DIR__ . '/../views/parttime/payslip_view_view.php';
