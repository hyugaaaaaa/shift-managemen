<?php render_header('定休日設定'); ?>

<div class="container mt-4">
    <h1 class="mb-4">定休日設定</h1>

    <?php if(!empty($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if(!empty($msg)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">定休日の選択</h5>
            <form method="get" class="d-flex align-items-center">
                <input type="month" name="month" class="form-control me-2" value="<?php echo htmlspecialchars($month); ?>" onchange="this.form.submit()">
            </form>
        </div>
        <div class="card-body">
            <p class="card-text">定休日に設定したい日付にチェックを入れて保存してください。<br>定休日に設定された日は、アルバイト従業員からのシフト希望提出が制限されます。</p>
            
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
                
                <div class="row row-cols-2 row-cols-md-4 row-cols-lg-6 g-3 mb-4">
                    <?php
                    $date_obj = new DateTime($start_date);
                    $end_obj = new DateTime($end_date);
                    
                    while ($date_obj <= $end_obj) {
                        $date_str = $date_obj->format('Y-m-d');
                        $day = $date_obj->format('j');
                        $w = (int)$date_obj->format('w');
                        $week_ja = ['日', '月', '火', '水', '木', '金', '土'];
                        $color_class = ($w === 0) ? 'text-danger' : (($w === 6) ? 'text-primary' : '');
                        $is_checked = in_array($date_str, $current_holidays) ? 'checked' : '';
                        
                        echo '<div class="col">';
                        echo '<div class="form-check p-3 border rounded bg-light position-relative">';
                        echo '<input class="form-check-input" type="checkbox" name="holidays[]" value="' . $date_str . '" id="date_' . $date_str . '" ' . $is_checked . '>';
                        echo '<label class="form-check-label w-100 stretched-link ' . $color_class . '" for="date_' . $date_str . '">';
                        echo $day . '日 (' . $week_ja[$w] . ')';
                        echo '</label>';
                        echo '</div>';
                        echo '</div>';
                        
                        $date_obj->modify('+1 day');
                    }
                    ?>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-primary btn-lg px-5">設定を保存</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php render_footer(); ?>
