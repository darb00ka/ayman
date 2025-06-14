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
                <div class="container">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="container mt-3 page-content">
                                <section class="dashboard">
                                    <div class="container-fluid">
                                        <h4 class="text-center font-weight-bold mb-4 mt-2">
                                            الرسائل الواردة من الزوار
                                        </h4>
                                        <?php
                                        $inbox = inbox($conn);
                                        if ($inbox['error']) {
                                            sendError($inbox['message']);
                                        } else {
                                        ?>
                                            <table class="table table-striped text-center">
                                                <thead>
                                                    <tr>
                                                        <th>م</th>
                                                        <th>عنوان الرسالة</th>
                                                        <th>المرسل</th>
                                                        <th>تاريخ الإرسال</th>
                                                    </tr>
                                                    <?php
                                                    if ($inbox['num'] == 0) {
                                                        emptyRow(6, 'لا يوجد أي شكاوى مستلمة حتى الآن');
                                                    } else {
                                                        $n = 1;
                                                        foreach ($inbox['messages'] as $message) {
                                                            inbox_row($n, $message);
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
        <script src="../js/notifications.js"></script>
        <script>
            AOS.init();
            $(function() {
                $(".view-message").click(function(){
                    var $this = $(this),
                        title = $this.attr('title'),
                        message = $this.attr('message'),
                        sender = $this.attr('sender'),
                        email = $this.attr('email'),
                        phone = $this.attr('phone'),
                        date = $this.attr('date');
                    notiModal(inboxMessage(title, sender, message, date, email, phone));
                })
            })
        </script>
    </body>
    
    </html>
    <?php
}