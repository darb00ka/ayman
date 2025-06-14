<?php
session_start();
include '../inc/func.php';
remember();
if(!$_SESSION['id']){
    header("Location: ../login");
}else{
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
        <div class="account-page">
            <?php include 'header.php' ?>
            <div class="container">
                <!-- Noti Modal -->
                <input type="text" value="<?=$_SESSION['acc_type']?>" id="accType" hidden="">
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
                            <section class="page-content">
                                <section class="profile p-5" id="profile">
                                    <div class="container">
                                        <h3 class="text-center font-weight-bold text-center">
                                            متفوتش الفرصة واضمن كل معاملاتك المالية من خلالنا
                                        </h3>
                                        <ul>
                                            <li>
                                                اذا كان ليس لديك رصيد فى حسابك يمكنك اضافه الرصيد الخاص بك لاتمام كل معاملاتك
                                            </li>
                                            <li>
                                                اذا كنت مشترى يمكنك انشاء معامله جديده مضمونه 
                                            </li>
                                            <li>
                                                اذا كنت بائع يمكنك قبول المعامله من الإشعارات واتمام معاملتك
                                            </li>
                                        </ul>
                                        <div class="row mt-3 text-center">
                                            <div class="col-md">
                                                <a href="account" class="btn btn-info grad-btn">إضافة رصيد</a>
                                            </div>
                                            <div class="col-md">
                                                <a href="new_order" class="btn btn-info grad-btn">إنشاء معاملة</a>
                                            </div>
                                            <div class="col-md">
                                                <a href="notifications" class="btn btn-info grad-btn">الإشعارات</a>
                                            </div>
                                        </div>
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
        
        <script src="../js/popper.min.js"></script>
        <script src="../js/ajax.min.js"></script>
        <script src="../js/bootstrap.min.js"></script>
        <script src="../js/aos.js"></script>
        <script src="../js/main.js"></script>
        <script src="../js/notifications.js"></script>
        <script>
        </script>
    </body>

    </html>
    <?php
}