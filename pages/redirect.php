<?php
session_start();
include '../inc/func.php';
remember();
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
    <div class="redirect-page">
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
                        <div class="pt-4 pb-4 rounded container mt-3 redirect text-center">
                            <h4 class="mb-4 font-weight-bold">
                                أنت على وشك مغادرة الموقع
                            </h4>
                            <p class="lead">
                                أنت الآن على وشك مغادة موقعنا الإلكتروني وزيارة موقع خارجي ، يرجى التأكد من عدم إدخال كلمة سر حسابك في أي موقع خارجي
                            </p>
                            <a href="<?= $_GET['link'] ?>" target="_blank" class="btn btn-info grad-btn">الإستمرار</a>
                        </div>
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
        AOS.init();
        $(function() {

        })
    </script>
</body>

</html>