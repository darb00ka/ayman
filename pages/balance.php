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
                                                الرصيد
                                            </h4>
                                            <p class="lead mt-3">
                                                يمكنك من هنا التعرف وإدارة كل الأمور المالية الخاصة بموقعك
                                                <ul class="">
                                                    <li>مراجعة طلبات الحسابات الجديدة وقبولها أو رفضها</li>
                                                    <li>إستعراض المبيعات والمشتريات لكل عميل</li>
                                                    <li>إستعراض المعاملات المالية لكل عميل</li>
                                                </ul>
                                            </p>
                                            <h6>ملخص الرصيد</h6>
                                            <?php
                                            $admin_balance = admin_balance($conn);
                                            if ($admin_balance['error']) {
                                                sendError($admin_balance['message']);
                                            } else {
                                            }
                                            ?>
                                            <table>
                                                <tr>
                                                    <th>إجمالي الأرصدة المضافة : </th>
                                                    <td><?= $admin_balance['plus'] ?> جنيه مصري</td>
                                                </tr>
                                                <tr>
                                                    <th>إجمالي الأرصدة المسحوبة : </th>
                                                    <td><?= $admin_balance['mins'] ?> جنيه مصري</td>
                                                </tr>
                                                <tr>
                                                    <th>إجمالي الأرصدة الحالية بالموقع : </th>
                                                    <td><?= $admin_balance['now'] ?> جنيه مصري</td>
                                                </tr>
                                                <tr>
                                                    <th>الأرباح : </th>
                                                    <td><?= $available ?> جنيه مصري</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </section>
                                    <ul class="nav nav-tabs mt-4">
                                        <li class="nav-item">
                                            <a class="nav-link content-switch active" href="#" id="btn-add-balance">طلبات
                                                إضافة الرصيد</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link content-switch" href="#" id="btn-withdraw-balance">طلبات سحب
                                                الرصيد
                                                الجديدة</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link content-switch" href="#" id="btn-process-balance">اخر
                                                المعاملات المالية</a>
                                        </li>
                                    </ul>
                                    <section class="balance-content">
                                        <table class="table table-responsive table-striped add-balance">
                                            <thead>
                                                <tr>
                                                    <th>م</th>
                                                    <th>الإسم</th>
                                                    <th>ID</th>
                                                    <th>رقم الهاتف</th>
                                                    <th>كود العملية</th>
                                                    <th>قبول</th>
                                                    <th>رفض</th>
                                                </tr>
                                            </thead>
                                            <?php
                                            $add_requests = requests($conn, 'add', 'waiting');
                                            if ($add_requests['error']) {
                                                $back['error'] = true;
                                                $back['message'] = $add_requests['message'];
                                            } else {
                                                if ($add_requests['check'] == 0) {
                                                    emptyRow(7, "لا يوجد طلبات إضافة رصيد حالياً");
                                                } else {
                                                    for ($i = 0; $i < count($add_requests['requests']); $i++) {
                                                        add_request_row($conn, $i + 1, $add_requests['requests'][$i]);
                                                    }
                                                }
                                            }
                                            ?>
    
                                        </table>
                                        <table class="table table-responsive table-striped withdraw-balance">
                                            <thead>
                                                <tr>
                                                    <th>م</th>
                                                    <th>الإسم</th>
                                                    <th>ID</th>
                                                    <th>رقم الهاتف</th>
                                                    <th>المبلغ المطلوب</th>
                                                    <th>قبول</th>
                                                </tr>
                                            </thead>
                                            <?php
                                            $withdraw_requests = requests($conn, 'withdraw', 'waiting');
                                            if ($withdraw_requests['error']) {
                                                $back['error'] = true;
                                                $back['message'] = $withdraw_requests['message'];
                                            } else {
                                                if ($withdraw_requests['check'] == 0) {
                                                    emptyRow(6, "لا يوجد طلبات سحب رصيد حالياً");
                                                } else {
                                                    for ($i = 0; $i < count($withdraw_requests['requests']); $i++) {
                                                        withdraw_request_row($conn, $i + 1, $withdraw_requests['requests'][$i]);
                                                    }
                                                }
                                            }
                                            ?>
                                        </table>
                                        <table class="table table-responsive table-striped process-balance">
                                            <thead>
                                                <tr>
                                                    <th>م</th>
                                                    <th>كود المعاملة</th>
                                                    <th>الإسم</th>
                                                    <th>ID</th>
                                                    <th>رقم الهاتف</th>
                                                    <th>نوع المعاملة</th>
                                                    <th>القيمة<br>(ج.م)</th>
                                                    <th>التاريخ</th>
                                                    <th>الوقت</th>
                                                </tr>
                                            </thead>
                                            <?php
                                            $finance_process = finance_process($conn, 'all', 100);
                                            if ($finance_process['error']) {
                                                $back['error'] = true;
                                                $back['message'] = $finance_process['message'];
                                            } else {
                                                if ($finance_process['check'] == 0) {
                                                    emptyRow(10, "لا يوجد معاملات مالية حالياً");
                                                } else {
                                                    for ($i = 0; $i < count($finance_process['process']); $i++) {
                                                        finance_process_row($conn, $i + 1, $finance_process['process'][$i]);
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
                $(".withdraw-balance, .process-balance").hide();
    
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
                                        window.location.href=`balance`;
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
                                window.location.href=`balance`;
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
                                window.location.href=`balance`;
                            }
                        })
                    }
                })
                $(".process-id").click(function(){
                    var $this = $(this),
                        code = $this.attr("code"),
                        name = $this.attr("name"),
                        phone = $this.attr("phone"),
                        type = $this.attr("type"),
                        value = $this.attr("value"),
                        reason = $this.attr("reason"),
                        date = $this.attr("date"),
                        hour = $this.attr("hour");
                    notiModal(financeProcessModal(code, name, phone, type, value, reason, date, hour));            
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