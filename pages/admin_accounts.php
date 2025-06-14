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
                <!-- Accept Modal -->
                <div class="modal fade" id="acceptModal" tabindex="-1" role="dialog"
                    aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLongTitle">تأكيد قبول الحساب</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body" id="acceptModal">
                                <div class="conainer-fluid">
                                    <form role="form">
                                        <div class="form-group">
                                            <label class="font-weight-bold" for="الرقم القومي"></label>
                                            <input type="text" placeholder="إدخل الرقم القومي للعميل هنا" id="accNid"
                                                class="form-control">
                                        </div>
                                        <div class="form-group mt-3">
                                            <button type="button" class="btn btn-block btn-info grad-btn" id="activeAccount">تأكيد
                                                التفعيل</button>
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
                                    <section class="accounts">
                                        <div class="container">
                                            <h4 class="text-center font-weight-bold">
                                                حسابات العملاء
                                            </h4>
                                            <p class="lead mt-3">
                                                يمكنك متابعة كافة عملائك من هنا، حيث يمكنك:
                                            <ul class="">
                                                <li>مراجعة طلبات الحسابات الجديدة وقبولها أو رفضها</li>
                                                <li>إستعراض المبيعات والمشتريات لكل عميل</li>
                                                <li>إستعراض المعاملات المالية لكل عميل</li>
                                            </ul>
                                            </p>
                                        </div>
                                    </section>
                                    <ul class="nav nav-tabs mt-4">
                                        <li class="nav-item">
                                            <a class="nav-link content-switch active" href="#" id="btn-new-accounts">طلبات الحسابات
                                                الجديدة</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link content-switch" href="#" id="btn-active-accounts">الحسابات المفعلة</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link content-switch" href="#" id="btn-banned-accounts">الحسابات
                                                المحظورة</a>
                                        </li>
                                    </ul>
                                    <section class="accounts-content">
                                        <table class="table table-responsive table-striped active-accounts">
                                            <thead>
                                                <tr>
                                                    <th>م</th>
                                                    <th>الإسم</th>
                                                    <th>ID</th>
                                                    <th>البريد الإلكتروني</th>
                                                    <th>رقم الهاتف</th>
                                                    <th>التوثيق</th>
                                                    <th>حظر المستخدم</th>
                                                </tr>
                                                <?php
                                                $active_accounts = accounts($conn, 'Active');
                                                if ($active_accounts['error']) {
                                                    sendError($active_accounts['message']);
                                                } else {
                                                    if ($active_accounts['num'] == 0) {
                                                        emptyRow(7, $active_accounts['message']);
                                                    } else {
                                                        $n = 1;
                                                        for ($i = 0; $i < count($active_accounts['accounts']); $i++) {
                                                            active_account_row($i + 1, $active_accounts['accounts'][$i]);
                                                        }
                                                    }
                                                }
                                                ?>
                                            </thead>

                                        </table>
                                        <table class="table table-responsive table-striped banned-accounts">
                                            <thead>
                                                <tr>
                                                    <th>م</th>
                                                    <th>الإسم</th>
                                                    <th>ID</th>
                                                    <th>البريد الإلكتروني</th>
                                                    <th>رقم الهاتف</th>
                                                    <th>إعادة تفعيل المستخدم</th>
                                                </tr>
                                                <?php
                                                $banned_accounts = accounts($conn, 'Banned');
                                                if ($banned_accounts['error']) {
                                                    sendError($banned_accounts['message']);
                                                } else {
                                                    if ($banned_accounts['num'] == 0) {
                                                        emptyRow(6, $banned_accounts['message']);
                                                    } else {
                                                        $n = 1;
                                                        for ($i = 0; $i < count($banned_accounts['accounts']); $i++) {
                                                            banned_account_row($i + 1, $banned_accounts['accounts'][$i]);
                                                        }
                                                    }
                                                }
                                                ?>
                                            </thead>

                                        </table>
                                        <table class="table table-responsive table-striped new-accounts">
                                            <thead>
                                                <tr>
                                                    <th>م</th>
                                                    <th>الإسم</th>
                                                    <th>ID</th>
                                                    <th>البريد الإلكتروني</th>
                                                    <th>رقم الهاتف</th>
                                                    <th>صورة البطاقة</th>
                                                    <th>قبول الطلب</th>
                                                    <th>رفض الطلب</th>
                                                </tr>
                                                <?php
                                                $new_accounts = accounts($conn, 'Waiting');
                                                if ($new_accounts['error']) {
                                                    sendError($new_accounts['message']);
                                                } else {
                                                    if ($new_accounts['num'] == 0) {
                                                        emptyRow(8, $new_accounts['message']);
                                                    } else {
                                                        $n = 1;
                                                        for ($i = 0; $i < count($new_accounts['accounts']); $i++) {
                                                            new_account_row($i + 1, $new_accounts['accounts'][$i]);
                                                        }
                                                    }
                                                }
                                                ?>
                                            </thead>

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
            $(".banned-accounts, .active-accounts").hide();
            $(".ban-account").click(function() {
                var $this = $(this),
                    id = $this.attr("account"),
                    status = "Banned";

                $.post('../inc/run.php', {
                    target: 'active-ban-account',
                    id: id,
                    status: status
                }, (data) => {
                    var data = JSON.parse(data);
                    if (data.error) {
                        notiModal(data.message);
                    } else {
                        $this.parent().parent().remove();
                    }
                })
            })
            $(".reactive-account").click(function() {
                var $this = $(this),
                    id = $this.attr("account"),
                    status = "Active";

                $.post('../inc/run.php', {
                    target: 'active-ban-account',
                    id: id,
                    status: status
                }, (data) => {
                    var data = JSON.parse(data);
                    if (data.error) {
                        notiModal(data.message);
                    } else {
                        $this.parent().parent().remove();
                    }
                })
            })
            $(".active-account").click(function() {
                var $this = $(this),
                    id = $this.attr("account");
                if (!id) {
                    notiModal(`<i class="fas fa-times text-danger ml-1"></i> خطأ، هناك بيانات غير مكتملة`);
                } else {
                    $("#acceptModal").modal('show');

                    $("#activeAccount").click(function() {
                        var nid = $("#accNid").val();
                        if (!nid) {
                            $("#acceptModal").modal('hide');
                            notiModal(
                                `<i class="fas fa-times text-danger ml-1"></i> يجب إدخال الرقم القومي، يرحى المحاولى مجدداً`
                                );
                        } else {
                            if (nid.length != 14) {
                                $("#acceptModal").modal('hide');
                                notiModal(
                                    `<i class="fas fa-times text-danger ml-1"></i> يجب أن يتكون الرقم القومي من 14 رقماً، يرحى المحاولى مجدداً`
                                    );
                            }else{
                                $.post('../inc/run.php', {
                                    target: 'active-account',
                                    id: id,
                                    nid: nid
                                }, (data) => {
                                    var data = JSON.parse(data);
                                    if (data.error) {
                                        notiModal(data.message);
                                    } else {
                                        $("#acceptModal").modal('hide');
                                        $this.parent().parent().remove();
                                        notiModal(`<i class="fas fa-check text-success ml-2"></i> تم تفعيل الحساب بنجاح`);
                                    }
                                })
                            }
                        }
                    })
                }
            })
            $(".action-account").click(function(){
                var $this = $(this),
                    account = $this.attr('account'),
                    action  = $this.attr('action');
                if(!account || !action){
                    notiModal(`<i class="fas fa-times text-danger ml-1"></i> خطأ، هناك بيانات غير مكتملة`);
                }else{
                    $.post('../inc/run.php', {target: "action-verify", id: account, action: action}, (data)=>{
                        var data = JSON.parse(data);
                        if(data.error){
                            notiModal(`<i class="fas fa-times text-danger ml-2"></i>${data.message}`);
                        }else{
                            notiModal(`<i class="fas fa-check text-success ml-2"></i>${data.message}`);
                        }
                    })
                }
            })
            // Switch content
            $(".content-switch").click(function(){
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