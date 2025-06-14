<?php
session_start();
include '../inc/func.php';
remember();
if (!$_SESSION['id']) {
    header("Location: ../login");
} else {
    $balance = balance($conn, $_SESSION['id']);
    $available = $balance['available'];
    $banned = $balance['banned'];
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <?php include 'head.php'?>
    </head>

    <body>
        
        
        <?php include 'header.php' ?>
        <div class="account-page" id="main">
            <div class="container">
                <!-- Noti Modal -->
                <div class="modal fade" id="notiModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLongTitle">تنبيه</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body" id="notiBody">
    
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">إغلاق</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Noti Modal -->
                <div class="container">
                    <?php
                    if (isset($_POST['request_add'])) {
                        $receipt = $_FILES['receipt'];
                        $upload  = new_receipt($receipt);
                        if ($upload['error']) {
                            sendError($upload['message']);
                        } else {
                            $add = add_request($conn, $upload['name']);
                            if ($add['error']) {
                                sendError($add['message']);
                            } else {
                                sendSucc("تم إرسال طلب إضافة الرصيد الخاص بك بنجاح");
                            }
                        }
                    }
                    ?>
                    <div class="row">
                        <div class="col-md-8">
                            <ul class="nav nav-tabs mt-4">
                                <li class="nav-item">
                                    <a class="nav-link content-switch active" href="#" id="btn-purches">المشتريات</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link content-switch" href="#" id="btn-sales">المبيعات</a>
                                </li>
                            </ul>
                            <section class="page-content">
                                <section class="purches" id="purches">
                                    <h4 class="mt-3 mb-2">المشتريات</h4>
                                    <div class="container">
                                        <?php
                                        $purches_orders = orders($conn, $_SESSION['id'], 'purches', '');
                                        if ($purches_orders['error']) {
                                            sendError($purches_orders['message']);
                                        } else {
                                            if ($purches_orders['num'] == 0) {
                                                echo "<p class='lead text-center font-weight-bold'>لا يوجد لديك مشتريات حتى الآن</p>";
                                            } else {
                                                foreach ($purches_orders['orders'] as $purches_process) {
                                                    echo purches_item($conn, $purches_process);
                                                }
                                            }
                                        }
                                        ?>
                                    </div>
                                </section>
                                <section class="sales" id="sales">
                                    <h4 class="mt-3 mb-2">المبيعات</h4>
                                    <div class="container">
                                        <?php
                                        $sales_orders = orders($conn, $_SESSION['id'], 'sales', '');
                                        if ($sales_orders['error']) {
                                            sendError($sales_orders['message']);
                                        } else {
                                            if ($sales_orders['num'] == 0) {
                                                echo "<p class='lead text-center font-weight-bold'>لا يوجد لديك مبيعات حتى الآن</p>";
                                            } else {
                                                foreach ($sales_orders['orders'] as $sales_process) {
                                                    echo sales_item($conn, $sales_process);
                                                }
                                            }
                                        }
                                        ?>
                                    </div>
                                </section>
                            </section>
    
                        </div>
                        <div class="col-md-4">
                            <?php include 'sidebar.php'; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
        
        <script src="../js/popper.min.js"></script>
        <script src="../js/ajax.min.js"></script>
        <script src="../js/bootstrap.min.js"></script>
        <script src="../js/aos.js"></script>
        <script src="../js/main.js"></script>
        <script src="../js/notifications.js"></script>
        <script>
            AOS.init();
            $(function() {
                $("#sales").hide();
                // Shifting tabs content
                $(".content-switch").click(function(e) {
                    e.preventDefault();
                    var $this = $(this),
                        id = $this.attr('id');
                    tabName = id.split('-'),
                        tabName = `#${tabName[1]}`;
                    $(tabName).siblings().hide();
                    $(tabName).fadeIn(600);
                    $this.parent().siblings().children().removeClass("active");
                    $this.addClass("active");
                })
            })

        </script>
    </body>

    </html>
<?php
}
