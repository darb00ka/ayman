<?php
session_start();
include '../inc/func.php';
remember();
if(!$_SESSION['id'] || $_SESSION['acc_type'] != "admin"){
    header("Location: ../login");
}else{
    $balance = balance($conn, $_SESSION['id']);
    $available = $balance['available'];
    $banned = $balance['banned'];
    $tax = tax($conn);
    $tax = $tax['tax'];
    ?>
    <!DOCTYPE html>
    <html lang="en">
    
    <head>
        <?php include 'head.php'?>
    </head>
    
    <body>
        <div class="admin-page">
            <?php include 'header.php' ?>
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
                <div class="modal fade" id="refuseModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLongTitle">رفض تسليم طلب</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body" id="refuseModal">
                                <div class="conainer-fluid">
                                    <form role="form">
                                        <div class="form-group">
                                            <label class="font-weight-bold">سبب الرفض:</label>
                                            <textarea id="refuseReason" style="height: 50px;" class="form-control" placeholder="يرجى شرح كافة الأسباب التي دفعتك لرفض إستلام الطلب"></textarea>
                                        </div>
                                        <div class="form-group mt-3">
                                            <button type="button" class="btn btn-block btn-info grad-btn" id="confirmRefuse">تأكيد عدم الإستلام</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">إغلاق</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="container">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="container mt-3 page-content">
                                <section class="dashboard">
                                    <div class="container-fluid">
                                        <h4 class="text-center font-weight-bold mb-4 mt-2">
                                            الشكاوى الواردة من العملاء
                                        </h4>
                                        <?php
                                        $admin_reports = admin_reports($conn);
                                        if ($admin_reports['error']) {
                                            sendError($admin_reports['message']);
                                        } else {
                                        ?>
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>م</th>
                                                        <th>عنوان الشكوى</th>
                                                        <th>كود الشكوى</th>
                                                        <th>العميل</th>
                                                        <th>تاريخ البدأ</th>
                                                        <th>الحالة</th>
                                                    </tr>
                                                    <?php
                                                    if($admin_reports['num'] == 0){
                                                        emptyRow(6, 'لا يوجد أي شكاوى مستلمة حتى الآن');
                                                    }else{
                                                        $n = 1;
                                                        foreach($admin_reports['reports'] as $report){
                                                            admin_report_row($n, $conn, $report);
                                                            $n++;
                                                        }
                                                    }
                                                    ?>
                                                </thead>
                                            </table>
                                            <?php
                                        }
                                            ?>
                                    </div>
                                </section>
                            </div>
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
        <script>
            AOS.init();
            $(function() {
    
            })
        </script>
    </body>
    
    </html>
    <?php
}