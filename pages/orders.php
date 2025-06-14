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
                <!-- Accept Modal -->
                <div class="modal fade" id="acceptAddModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLongTitle">تأكيد قبول الحساب</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body" id="acceptAddModal">
                                <div class="conainer-fluid">
                                    <form role="form">
                                        <div class="form-group">
                                            <label class="font-weight-bold">المبلغ المضاف</label>
                                            <input type="text" placeholder="إدخل المبلغ الذي تريد إضافته لحساب العميل هنا..." id="addVal" class="form-control">
                                        </div>
                                        <div class="form-group mt-3">
                                            <button type="button" class="btn btn-block btn-info grad-btn" id="acceptAdd">تأكيد
                                                إضافة الرصيد</button>
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
                <!-- End Accept Modal -->
                <div class="container">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="container-fluid mt-3">
                                <div class="page-content">
                                    <section class="balance">
                                        <div class="container">
                                            <h4 class="text-center font-weight-bold">
                                                الطلبات
                                            </h4>
                                            <p class="lead mt-3">
                                                يمكنك من هنا التعرف وإدارة كل الطلبتات بإختلاف تصنيفاتها على موقعك وتظهر لك الطلبات مصنفة كالتالي:
                                                <ul>
                                                    <li>طلبات جاري تنفيذها</li>
                                                    <li>طلبات في إنتظار موافقة المشتري</li>
                                                    <li>طلبات تم تسليمها</li>
                                                    <li>طلبات ملغية</li>
                                                </ul>
                                            </p>
                                        </div>
                                    </section>
                                    <ul class="nav nav-tabs mt-4">
                                        <li class="nav-item">
                                            <a class="nav-link content-switch active" href="#" id="btn-running-orders">طلبات
                                                جاي تنفيذها</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link content-switch" href="#" id="btn-waiting-orders">طلبات في إنتظار الموافقة</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link content-switch" href="#" id="btn-submitted-orders">طلبات تم تسليمها</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link content-switch" href="#" id="btn-cancelled-orders">طلبات ملغية</a>
                                        </li>
                                    </ul>
                                    <section class="orders-content">
                                        <table class="table table-responsive table-striped running-orders">
                                            <thead>
                                                <tr>
                                                    <th>م</th>
                                                    <th>العنوان</th>
                                                    <th>ID</th>
                                                    <th>التصنيف</th>
                                                    <th>البائع</th>
                                                    <th>المشتري</th>
                                                    <th>السعر</th>
                                                    <th>موعد التسليم المتوقع</th>
                                                </tr>
                                            </thead>
                                            <?php
                                            $running_orders = admin_orders($conn, 'running');
                                            if ($running_orders['error']) {
                                                $back['error'] = true;
                                                $back['message'] = $running_orders['message'];
                                            } else {
                                                if ($running_orders['num'] == 0) {
                                                    emptyRow(7, "لا يوجد طلبات جاري تنفيذها حالياً");
                                                } else {
                                                    for ($i = 0; $i < count($running_orders['orders']); $i++) {
                                                        admin_order_row($conn, $i + 1, $running_orders['orders'][$i]);
                                                    }
                                                }
                                            }
                                            ?>
    
                                        </table>
                                        <table class="table table-responsive table-striped waiting-orders">
                                            <thead>
                                                <tr>
                                                    <th>م</th>
                                                    <th>العنوان</th>
                                                    <th>ID</th>
                                                    <th>التصنيف</th>
                                                    <th>البائع</th>
                                                    <th>المشتري</th>
                                                    <th>السعر</th>
                                                    <th>موعد التسليم المتوقع</th>
                                                </tr>
                                            </thead>
                                            <?php
                                            $waiting_orders = admin_orders($conn, 'waiting acceptance');
                                            if ($waiting_orders['error']) {
                                                $back['error'] = true;
                                                $back['message'] = $waiting_orders['message'];
                                            } else {
                                                if ($waiting_orders['num'] == 0) {
                                                    emptyRow(7, "لا يوجد طلبات في إنتظار موافقة المشتري حالياً");
                                                } else {
                                                    for ($i = 0; $i < count($waiting_orders['orders']); $i++) {
                                                        admin_order_row($conn, $i + 1, $waiting_orders['orders'][$i]);
                                                    }
                                                }
                                            }
                                            ?>
                                        </table>
                                        <table class="table table-responsive table-striped submitted-orders">
                                            <thead>
                                                <tr>
                                                    <th>م</th>
                                                    <th>العنوان</th>
                                                    <th>ID</th>
                                                    <th>التصنيف</th>
                                                    <th>البائع</th>
                                                    <th>المشتري</th>
                                                    <th>السعر</th>
                                                    <th>موعد التسليم المتوقع</th>
                                                </tr>
                                            </thead>
                                            <?php
                                            $submitted_orders = admin_orders($conn, 'subumitted');
                                            if ($submitted_orders['error']) {
                                                $back['error'] = true;
                                                $back['message'] = $submitted_orders['message'];
                                            } else {
                                                if ($submitted_orders['num'] == 0) {
                                                    emptyRow(8, "لا يوجد طلبات تم تسليمها حالياً");
                                                } else {
                                                    for ($i = 0; $i < count($submitted_orders['orders']); $i++) {
                                                        admin_order_row($conn, $i + 1, $submitted_orders['orders'][$i]);
                                                    }
                                                }
                                            }
                                            ?>
                                        </table>
                                        <table class="table table-responsive table-striped cancelled-orders">
                                            <thead>
                                                <tr>
                                                    <th>م</th>
                                                    <th>العنوان</th>
                                                    <th>ID</th>
                                                    <th>البائع</th>
                                                    <th>المشتري</th>
                                                    <th>السعر</th>
                                                    <th>موعد التسليم المتوقع</th>
                                                </tr>
                                            </thead>
                                            <?php
                                            $cancelled_orders = admin_orders($conn, 'cancelled');
                                            if ($cancelled_orders['error']) {
                                                $back['error'] = true;
                                                $back['message'] = $cancelled_orders['message'];
                                            } else {
                                                if ($cancelled_orders['num'] == 0) {
                                                    emptyRow(8, "لا يوجد طلبات ملغية حالياً");
                                                } else {
                                                    for ($i = 0; $i < count($cancelled_orders['orders']); $i++) {
                                                        admin_order_row($conn, $i + 1, $cancelled_orders['orders'][$i]);
                                                    }
                                                }
                                            }
                                            ?>
                                        </table>
                                    </section>
                                </div>
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
                $(".waiting-orders, .submitted-orders, .cancelled-orders").hide();
    
                $(".accept-add-request").click(function() {
                    var $this = $(this),
                        request = $this.attr('request');
                    if (!request) {
                        notiModal(`<i class="fas fa-times text-danger ml-2"></i>خطأ، هناك بيانات غير مكتملة`);
                    } else {
                        $("#acceptAddModal").modal("show");
                        $("#acceptAdd").click(function() {
                            var val = $("#addVal").val();
                            if (!val || !$.isNumeric(val)) {
                                $("#acceptAddModal").modal("hide");
                                notiModal(
                                    `<i class="fas fa-times text-danger ml-2"></i>يجب إدخال قيمة المبلغ المضاف من خلال العملية المشار إليها`
                                );
                            } else {
                                $.post('../inc/run.php', {
                                    target: "accept-add-request",
                                    val: val,
                                    request: request
                                }, (data) => {
                                    var data = JSON.parse(data);
                                    if (data.error) {
                                        $("#acceptAddModal").modal("hide");
                                        notiModal(
                                            `<i class="fas fa-times text-danger ml-2"></i>${data.message}`
                                        );
                                    } else {
                                        $("#acceptAddModal").modal("hide");
                                        $this.parent().parent().remove();
                                        notiModal(
                                            `<i class="fas fa-check text-success ml-2"></i> تم إضافة الرصيد بنجاح`
                                        );
                                    }
                                })
                            }
                        })
                    }
                })
    
                $(".refuse-add-request").click(function() {
                    var $this = $(this),
                        request = $this.attr('request');
                    if (!request) {
                        notiModal(`<i class="fas fa-times text-danger ml-2"></i>خطأ، هناك بيانات غير مكتملة`);
                    } else {
                        $.post('../inc/run.php', {
                            target: "refuse-add-request",
                            request: request
                        }, (data) => {
                            var data = JSON.parse(data);
                            if (data.error) {
                                notiModal(
                                    `<i class="fas fa-times text-danger ml-2"></i>${data.message}`);
                            } else {
                                $this.parent().parent().remove();
                                notiModal(
                                    `<i class="fas fa-check text-success ml-2"></i> تم رفض طلب الإضافة بنجاح`
                                );
                            }
                        })
                    }
                })
    
                $(".accept-withdraw-request").click(function() {
                    var $this = $(this),
                        request = $this.attr("request");
    
                    if (!request) {
                        notiModal(`<i class="fas fa-times text-danger ml-2"></i>خطأ، هناك بيانات غير مكتملة`);
                    } else {
                        $.post('../inc/run.php', {
                            target: "accept-withdraw-request",
                            request: request
                        }, (data) => {
                            var data = JSON.parse(data);
                            if (data.error) {
                                notiModal(
                                    `<i class="fas fa-times text-danger ml-2"></i>${data.message}`);
                            } else {
                                notiModal(
                                    `<i class="fas fa-check text-success ml-2"></i> تم خصم الرصيد بنجاح`
                                );
                                $this.parent().parent().remove();
                            }
                        })
                    }
                })
                // Switch content
                $(".content-switch").click(function() {
                    var $this = $(this),
                        btnId = $this.attr('id').split('-'),
                        tableClass = `.${btnId[1]}-${btnId[2]}`;
                    $this.parent().siblings().children().removeClass("active");
                    $this.addClass("active");
                    $(tableClass).siblings('table').hide();
                    $(tableClass).fadeIn(500);
                })
            })
        </script>
    </body>
    
    </html>
    <?php
}