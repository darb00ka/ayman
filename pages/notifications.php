<?php
session_start();
include '../inc/func.php';
remember();
if(!$_SESSION['id']){
    header("Location: ../login");
}else{
    visit_noti($conn);
    $balance = balance($conn, $_SESSION['id']);
    $available = $balance['available'];
    $banned = $balance['banned'];
    ?>
    <!DOCTYPE html>
    <html lang="en">
    
    <head>
        <?php include 'head.php'?>
    </head>
    
    <body >
        <div class="notifications-page">
            <?php include 'header.php' ?>
            <div class="container">
                <!-- Noti Modal -->
                <div class="modal fade" id="notiModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
                    aria-hidden="true">
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
                            <div class="container mt-3">
                                <section class="notifications rounded">
                                    <h4>
                                        <span class="fa-stack fa-lg text-center text-info" style="font-size: 0.9em;">
                                            <i class="fas fa-circle fa-stack-2x"></i>
                                            <i class="fas fa-bell fa-stack fa-inverse"></i>
                                        </span>
                                        الإشعارات
                                    </h4>
                                    <hr>    
                                    <?php
                                    $notifications = notifications($conn);
                                    if($notifications['error']){
                                        sendError($notifications['message']);
                                    }else{
                                        if($notifications['num'] == 0){
                                            ?>
                                            <p class="lead text-center mt-3 mb-3 font-weight-bold">لا يوجد لديك إشعارات حتى الآن</p>
                                            <?php
                                        }else{
                                            foreach($notifications['notifications'] as $notification){
                                                notification_row($notification);
                                            }
                                        }
                                    }
                                    ?>
                                </section>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <?php include 'sidebar.php';?>
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
            $(".notifications-item:lt(<?=safe($_GET['num'], 'int')?>)").addClass("new-notification");
        })
        </script>
    </body>
    
    </html>
    <?php
}