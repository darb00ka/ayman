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
    
    <body>
        <div class="order-page">
            <?php include 'header.php' ?>
            <div class="container">
                <!-- Noti Modal -->
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
                            <div class="container mt-3">
                                <?php
                                $order = safe($_GET['id'], 'int');
                                if (!$order) {
                                    sendError("هذا الطلب غير موجود يرجى التأكد من الرابط");
                                } else {
                                    $order = order($conn, $order);
                                    if ($order['error']) {
                                        sendError($order['message']);
                                    } else {
                                        $order = $order['order'];
                                        $permissions = order_permissions($order);
                                        if ($permissions['error']) {
                                            sendError($permissions['message']);
                                        } else {
                                            $seller = is_user($conn, $order['seller']);
                                            $seller = $seller['user'];
                                            $buyer = is_user($conn, $order['buyer']);
                                            $buyer = $buyer['user'];
                                            $attachments = order_attachments($conn, $order['id']);
                                            $messages = messages($conn, $order['id'], 'order');
                                ?>
                                            <div class="order">
                                                <?php
                                                if (isset($_POST['upload-attachment'])) {
                                                    $file = $_FILES['attachment'];
                                                    $uplaod = new_attachment($conn, $order['id'], $file);
                                                    if ($uplaod['error']) {
                                                        sendError($uplaod['message']);
                                                    } else {
                                                        sendSucc("قد تم إرسال الملف الخاص بك بنجاح");
                                                    }
                                                }
                                                ?>
                                                <section class="summary">
                                                    <table class="table tabke-borderless table-responsive">
                                                        <thead>
                                                            <tr>
                                                                <th colspan="5"><?= $order['title'] ?></th>
                                                            </tr>
                                                            <tr>
                                                                <td>رقم الطلب<br><?=$order['id']?></td>
                                                                <?php
                                                                switch ($permissions['type']) {
                                                                    case 'buyer':
                                                                ?>
                                                                        <td>البائع: <br> <?= short_name($seller) ?></td>
                                                                    <?php
                                                                        break;
                                                                    case 'seller':
                                                                    ?>
                                                                        <td>المشتري: <br> <?= short_name($buyer); ?></td>
                                                                    <?php
                                                                        break;
                                                                    case 'admin':
                                                                    ?>
                                                                        <td>البائع: <br> <?= short_name($seller) ?></td>
                                                                        <td>المشتري: <br> <?= short_name($buyer); ?></td>
                                                                <?php
                                                                        break;
                                                                }
                                                                ?>
                                                                <td>التكلفة: <br><?= $order['price'] ?> ج.م</td>
                                                                <td>الحالة: <br> <?= view_order_status($order['order_status']) ?></td>
                                                                <td>موعد التسليم: <br> <?= arabicDate($order['order_end']) ?> </td>
                                                                <td>إنتهاء الضمان: <br> <?= arabicDate($order['warranty_end']) ?></td>
                                                            </tr>
                                                        </thead>
                                                    </table>
                                                </section>
                                                <section class="details">
                                                    <h6>التفاصيل:</h6>
                                                    <p>
                                                        <?= nl2br($order['description']) ?>
                                                    </p>
                                                </section>
                                                <section class="messages">
                                                    <h5 class="mb-3">الرسائل:</h5>
                                                    <?php
                                                    if ($messages['num'] == 0) {
                                                    ?>
                                                        <p class="lead font-weight-bold text-center">لا يوجد رسائل في هذا الطلب حتى الآن</p>
                                                    <?php
                                                    } else {
                                                        foreach ($messages['messages'] as $message) {
                                                            echo message_item($conn, $message);
                                                        }
                                                    }
                                                    ?>
                                                </section>
                                                <section class="new-message-area">
                                                    <form role="form">
                                                        <div class="form-group">
                                                            <label for="newMessage">الرسالة:</label>
                                                            <textarea id="newMessage" class="form-control" placeholder="إكتب كل ما تود أن تكتبه في الرسالة هنا ثم إضغط زر 'إرسال' أدناه"></textarea>
                                                            <input type="text" id="orderId" value="<?= $order['id'] ?>" hidden>
                                                        </div>
                                                        <div class="form-group">
                                                            <button type="button" id="sendMessage" class="btn btn-info grad-btn">
                                                                إرسال الرسالة
                                                            </button>
                                                        </div>
                                                    </form>
                                                    <form method="post" enctype="multipart/form-data">
                                                        <div class="container">
                                                            <div class="row">
                                                                <div class="col-sm-9">
                                                                    <div class="form-group">
                                                                        <label for="newAttachment">إختر الملف</label>
                                                                        <input type="file" name="attachment" class="form-control form-control-file">
                                                                        <input type="text" name="target" value="upload-order-attachment" hidden>
                                                                    </div>
                                                                </div>
                                                                <div class="col-sm-3">
                                                                    <div class="form-group" style="margin-top: 27px;">
                                                                        <button type="submit" name="upload-attachment" class="btn btn-info btn-sm grad-btn mt-2">
                                                                            <i class="fas fa-upload ml-1"></i>
                                                                            رفع الملف
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </section>
                                                <section class="actions">
                                                    <div class="container-fluid">
                                                        <input type="text" id="orderBuyer" value="<?=$order['buyer']?>" hidden>
                                                        <h5 class="font-weight-bold mb-3">
                                                            الإجراءات المتاحة:
                                                        </h5>
                                                        <?php
                                                        if ($permissions['num'] == 0) {
                                                        ?>
                                                            <p class="lead text-center">
                                                                لا يوجد أي إجراءات يمكن إتخاذها متاحة لك حاليا لهذا الطلب
                                                            </p>
                                                        <?php
                                                        } else {
                                                            foreach ($permissions['permissions'] as $permit) {
                                                                echo action_btn($order['id'], $permit);
                                                            }
                                                        }
                                                        if ($_SESSION['acc_type'] == "admin") {
                                                            $warranty = order_warranty($conn, $order['id']);
                                                            if ($warranty['error']) {
                                                                sendError($warranty['message']);
                                                            } else {
                                                                if ($warranty['warranty']) {
                                                                    echo action_btn($order['id'], "cancel");
                                                                }
                                                            }
                                                        }
                                                        ?>
                                                    </div>
                                                </section>
                                                <section class="attachments">
                                                    <h6>المرفقات (<?= $attachments['num'] ?>):</h6>
                                                    <div class="container">
                                                        <div class="row mt-3">
                                                            <?php
                                                            if ($attachments['num'] < 1) {
                                                                echo $attachments['message'];
                                                            } else {
                                                                foreach ($attachments['attachments'] as $attachment) {
                                                                    echo attachment_item($attachment['old_name'], $attachment['new_name']);
                                                                }
                                                            }
                                                            ?>
                                                        </div>
                                                    </div>
                                                </section>
                                            </div>
                                <?php
                                        }
                                    }
                                }
                                ?>
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
        <script src="../js/jquery.scrollTo.min.js"></script>
        <script>
            AOS.init();
            $(function() {
                $(".messages-item").each(function(i){
                    var item = $(this),
                    height = item.find('.message').height() + 50;
                    item.height(height)
                })
                $("#sendMessage").click(function() {
                    var toId = $("#orderId").val(),
                        type = "order",
                        newMessage = $("#newMessage").val();
    
                    if (!newMessage || !toId) {
                        notiModal(`<i class="fas fa-times text-danger ml-2"></i> يجب إدخال كافة البيانات`);
                        console.log(`id: ${toId}, and newMessage: ${newMessage}`);
                    } else {
                        $.post('../inc/run.php', {
                            target: "new-message",
                            type: type,
                            for_id: toId,
                            new_message: newMessage
                        }, (data) => {
                            var data = JSON.parse(data);
                            if (data.error) {
                                notiModal(data.message);
                            } else {
                                $(".messages").find(".newst-message").removeClass("newst-message");
                                var item = `<table class="newst-message table table-responsive table-borderless message-table text-light p-2 mb-3 mess-sender">
                                            <tr>
                                                <th class="text-center">
                                                    ${data.sender_name} <br>
                                                    [${data.sender}]
                                                </th>
                                                <td class="message-text">
                                                ${data.mess}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-center" colspan="2">
                                                    <i class="fas fa-calendar-alt ml-1"></i> : ${data.date}
                                                    <i class="fas fa-clock ml-1"></i> : ${data.time}
                                                </td>
                                            </tr>
                                        </table>`;
                                $(".messages").append(item);
                                $("#newMessage").val("");
                                $('.messages').scrollTo('.newst-message');
                            }
                        })
                    }
                })
                $("#submitOrder").click(function() {
                    var $this = $(this),
                        order = $this.attr('order');
                    if(!order){
                        notiModal(`<i class="fas fa-times text-danger ml-2"></i> يرجى إعادة تحميل الصفحة والمحاولة مجدداً`);
                    }else{
                        $.post('../inc/run.php', {target: "submit-order", order_id: order}, (data)=>{
                            var data = JSON.parse(data);
                            if(data.error){
                                notiModal(`<i class="fas fa-times text-danger ml-2"></i> ${data.message}`);
                            }else{
                                window.location.href=`order.php?id=${order}`;
                            }
                        })
                    }
                })
                $("#acceptOrder").click(function() {
                    var $this = $(this),
                        order = $this.attr('order');
                    if(!order){
                        notiModal(`<i class="fas fa-times text-danger ml-2"></i> يرجى إعادة تحميل الصفحة والمحاولة مجدداً`);
                    }else{
                        $.post('../inc/run.php', {target: "accept-order", order_id: order}, (data)=>{
                            var data = JSON.parse(data);
                            if(data.error){
                                notiModal(`<i class="fas fa-times text-danger ml-2"></i> ${data.message}`);
                            }else{
                                window.location.href=`order.php?id=${order}`;
                            }
                        })
                    }
                })
                $("#cancelOrder").click(function() {
                    var $this = $(this),
                        order = $this.attr('order');
                    if(!order){
                        notiModal(`<i class="fas fa-times text-danger ml-2"></i> يرجى إعادة تحميل الصفحة والمحاولة مجدداً`);
                    }else{
                        $.post('../inc/run.php', {target: "cancel-order", order_id: order}, (data)=>{
                            var data = JSON.parse(data);
                            if(data.error){
                                notiModal(`<i class="fas fa-times text-danger ml-2"></i> ${data.message}`);
                            }else{
                                window.location.href=`order.php?id=${order}`;
                            }
                        })
                    }
                })
                $("#refuseOrder").click(function(){
                    var $this = $(this),
                        order = $this.attr('order'),
                        user = $("#orderBuyer").val();
                    if(!order || !user){
                        notiModal(`<i class="fas fa-times text-danger ml-2"></i> يرجى إعادة تحميل الصفحة والمحاولة مجدداً`);
                    }else{
                        $("#refuseModal").modal('show');
                        $("#confirmRefuse").click(function(){
                            var reason = $("#refuseReason").val(),
                                order = $this.attr('order');  
                            if(!reason){
                                $("#refuseModal").modal("hide");
                                notiModal(`<i class="fas fa-times text-danger ml-2"></i> يجب عليك إدخال كافة الأسباب التي دفعتك لرفض الإستلام لنتمكن من مراجعة طلبك بشكل صحيح والتأكد من ضمان حقك وحق البائع كذلك`);
                            }else{
                                var title = `أرفض إستلام الطلب رقم #${order}`,
                                    description = `أسباب رفض الطلب: \n ${reason} \n يمكنك الإطلاع على الطلب من الرابط ادناه\n https://wastetco.com/pages/order?id=${order}`;
                                $.post('../inc/run.php', {target: "refuse-order", order_id: order, reason: description}, (data)=>{
                                    var data = JSON.parse(data);
                                    if(data.error){
                                        notiModal(`<i class="fas fa-times text-danger ml-2"></i> ${data.message}`);
                                    }else{
                                        $this.fadeOut(600, function(){$this.remove()});
                                        notiModal(`<i class="fas fa-check text-success ml-2"></i> تم إرسال شكوتك بنجاح وسيتم مراجعتها والحسم فيها خلال ال24 ساعه القادمة`);
                                    }
                                })
                            }
                        })
                    }
                })
            })
        </script>
    </body>
    
    </html>
    <?php
}