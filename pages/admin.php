<?php
session_start();
include '../inc/func.php';
remember();
if(!$_SESSION['id'] || $_SESSION['acc_type'] != "admin"){
    header("Location: ../login.php"); 
    exit();
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
                                        <div class="row">
                                            <div class="col-sm-4">
                                                <a href="admin_accounts" class="dashboard-link text-center">
                                                    <div class="container-fluid text-center">
                                                        <img src="../img/accounts.webp" alt="accounts icon" class="text-center mb-2">
                                                        <h5 class="font-weight-bold text-center">حسابات العملاء</h5>
                                                    </div>
                                                </a>
                                            </div>
                                            <div class="col-sm-4">
                                                <a href="balance" class="dashboard-link text-center">
                                                    <div class="container-fluid text-center">
                                                        <img src="../img/balance.webp" alt="accounts icon" class="text-center mb-2">
                                                        <h5 class="font-weight-bold text-center">الأرصدة</h5>
                                                    </div>
                                                </a>
                                            </div>
                                            <div class="col-sm-4">
                                                <a href="orders" class="dashboard-link text-center">
                                                    <div class="container-fluid text-center">
                                                        <img src="../img/orders.webp" alt="accounts icon" class="text-center mb-2">
                                                        <h5 class="font-weight-bold text-center">الطلبات</h5>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="row mt-4">
                                            <div class="col-sm-4">
                                                <a href="admin_support" class="dashboard-link text-center">
                                                    <div class="container-fluid text-center">
                                                        <img src="../img/support.webp" alt="accounts icon" class="text-center mb-2">
                                                        <h5 class="font-weight-bold text-center">الدعم الفني</h5>
                                                    </div>
                                                </a>
                                            </div>
                                            <div class="col-sm-4">
                                                <a href="inbox" class="dashboard-link text-center">
                                                    <div class="container-fluid text-center">
                                                        <img src="../img/inbox.webp" alt="accounts icon" class="text-center mb-2">
                                                        <h5 class="font-weight-bold text-center">علبة الوارد</h5>
                                                    </div>
                                                </a>
                                            </div>
                                            <div class="col-sm-4">
                                                <a href="cat" class="dashboard-link text-center">
                                                    <div class="container-fluid text-center">
                                                        <img src="../img/category.webp" alt="category icon" class="text-center mb-2">
                                                        <h5 class="font-weight-bold text-center">إدارة التصنيفات</h5>
                                                    </div>
                                                </a>
                                            </div>
                                            <div class="col-sm-4 mt-4"> <a href="admin_locations.php" class="dashboard-link text-center">
                                                    <div class="container-fluid text-center">
                                                        <img src="../img/location.webp" alt="Location icon" class="text-center mb-2" style="width: 50px; height: 50px;"> <h5 class="font-weight-bold text-center">مواقع العملاء</h5>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </section>
                                <section class="setting">
                                    <h4 class="mb-3">
                                        تحديث الإعدادات
                                    </h4>
                                    <form role="form">
                                        <div class="form-group">
                                            <label for="newTax">العمولة</label>
                                            <input type="text" value="<?= $tax ?>" id="newTax" class="form-control">
                                        </div>
                                        <div class="form-group">
                                            <button type="button" class="btn btn-success" id="updateTax">تحديث العمولة</button>
                                        </div>
                                    </form>
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
        
        <script src="../js/popper.min.js"></script>
        <script src="../js/ajax.min.js"></script>
        <script src="../js/bootstrap.min.js"></script>
        <script src="../js/aos.js"></script>
        <script src="../js/main.js"></script>
        <script src="../js/notifications.js"></script>
        <script>
            AOS.init();
            $(function() {
                if($(window).width() < 768){
                    // ملاحظة: هذا الكود يقوم بإعادة التوجيه لـ wastetco.com إذا كان عرض الشاشة أقل من 768px.
                    // ولكن بما أن لديك نسخة للجوال m.wastetco.com، قد تحتاج لتوجيه المستخدمين إليها بدلاً من ذلك.
                    // window.location.replace("https://wastetco.com");
                }
                $("#updateTax").click(() => {
                    var newTax = $("#newTax").val();
                    if (!newTax) {
                        notiModal(`<i class="fas fa-times text-danger ml-2"></i> لا يمكن ترك حقل العمولة فارغاً`);
                    } else {
                        if (!$.isNumeric(newTax)) {
                            notiModal(`<i class="fas fa-times text-danger ml-2"></i> يجب أن تكون العمولة رقماً`);

                        } else {
                            if (newTax < 100 && newTax > 0) {
                                $.post('../inc/run.php', {
                                    target: "update-tax",
                                    new_tax: newTax
                                }, (data) => {
                                    var data = JSON.parse(data);
                                    if (data.error) {
                                        notiModal(`<i class="fas fa-times text-danger ml-2"></i> ${data.message}`);
                                    } else {
                                        notiModal(`<i class="fas fa-check text-success ml-2"></i> ${data.message}`);
                                    }
                                })
                            } else {
                                notiModal(`<i class="fas fa-times text-danger ml-2"></i> يجب أن تكون قيمة العمولة أكبر من "0" وأقل من "100"`);
                            }
                        }
                    }
                })
            })
        </script>
    </body>

    </html>
<?php
}
?>