<div class="sidebar mt-3">
    <div class="sidebar-part">
        <h5 class="sidebar-title">
            اخر المعاملات المالية:
        </h5>
        <ul class="list-unstyled last-finance-process">
            <?php
                $sidebar_finance_process = finance_process($conn, $_SESSION['id'], 5);
                if($sidebar_finance_process['check'] == 0){
                    echo "<p class='lead pb-2 text-center'>لا يوجد معاملات حتى الآن</p>";
                }else{
                    foreach($sidebar_finance_process['process'] as $sidebar_process){
                        echo sidebar_process_item($sidebar_process);
                    }
                }
            ?>
        </ul>
    </div>
    <div class="sidebar-part">
        <h5 class="sidebar-title">
            أخر الطلبات
        </h5>
        <ul class="list-unstyled last-orders">
            <?php
            $sidebar_orders = orders($conn, $_SESSION['id'], 'all', 5);
            if($sidebar_orders['num'] == 0){
                echo "<p class='lead pb-2 text-center'>لا يوجد طلبات حتى الآن</p>";
            }else{
                foreach($sidebar_orders['orders'] as $sidebar_order){
                    echo sidebar_order_item($conn, $sidebar_order);
                }
            }
            ?>
        </ul>
    </div>
</div>