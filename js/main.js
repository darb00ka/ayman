function notiModal(content) {
  $("#notiBody").html(content);
  $("#notiModal").modal("show");
}

// ITEMS FUNCTIONS
function money_item(type, value, why, date, time) {
  var icon, color, typeView;
  switch (type) {
    case "ban":
      icon = "ban";
      color = "danger";
      typeView = "رهن";
      break;
    case "unabn":
      icon = "ban";
      color = "success";
      typeView = "فك رهن";
      break;
    case "plus":
      icon = "plus";
      color = "success";
      typeView = "إضافة";
      break;
    case "mins":
      icon = "mins";
      color = "danger";
      typeView = "خصم";
      break;
    default:
      break;
  }
  var MoneyItem = `<tr>
      <td>
          <i class="fas fa-${icon} text-${color} ml-1"></i> ${typeView}
      </td>
      <td>${value} ج.م</td>
      <td>
          ${why}
      </td>
      <td>${date}</td>
      <td>${time}</td>
    </tr>`;
  return MoneyItem;
}
function purches_item(id, title, sellerName, status, price, end, warranty) {
  var purchesItem = `<div class="purches-item p-2">
      <a href="order.php?id=${id}" class="lead text-dark font-weight-bold">${title}</a>
      <table class="table table-borderless">
          <tr>
              <td>البائع: <br>${sellerName}</td>
              <td>الحالة: <br>${status}</td>
              <td>التكلفة: <br> ${price} ج.م</td>
              <td>ميعاد التسليم: <br>${end}</td>
              <td>نهاية الضمان: <br>${warranty}</td>
          </tr>
      </table>
    </div>`;
  return purchesItem;
}
function sales_item(
  id,
  title,
  buyerName,
  status,
  price,
  sellerVal,
  end,
  warranty
) {
  var salesItem = `<div class="sales-item p-2">
      <a href="order.php?id=${id}" class="lead text-dark font-weight-bold">${title}</a>
      <table class="table table-borderless">
          <tr>
              <td>المشتري: <br>${buyerName}</td>
              <td>الحالة: <br>${status}</td>
              <td>التكلفة: <br> ${price} ج.م (${sellerVal} ج.م)</td>
              <td>ميعاد التسليم: <br> ${end}</td>
              <td>نهاية الضمان: <br>${warranty}</td>
          </tr>
      </table>
    </div>`;
  return salesItem;
}
function attachment_item(oldName, newName) {
  var item = `<a href="../attachments/${newName}" class="btn btn-sm btn-outline-secondary mb-3">
          <i class="fas fa-file ml-2"></i>
          ${oldName}
      </a>`;
  return item;
}
function sendMessage(type, forId, newMessage) {
  if (!newMessage || !forId) {
    notiModal(
      `<i class="fas fa-times text-danger ml-2"></i> يجب إدخال افة البيانات`
    );
  } else {
    $.post(
      "../inc/run.php",
      {
        target: "new-message",
        type: type,
        for_id: forId,
        new_message: newMessage,
      },
      (data) => {
        var data = JSON.parse(data);
        if (data.error) {
          notiModal(data.message);
        } else {
          var item = `<div class="messages-item">
            <div class="container">
                <div class="user-area text-center">
                    <span class="fa-stack fa-2x d-block mb-2">
                        <i class="fas fa-circle fa-stack-2x"></i>
                        <i class="fas fa-user fa-stack fa-1x fa-inverse"></i>
                    </span>
                    ${data.sender_name} <br>
                    (${data.sender})
                </div>
                <div class="message-area">
                    <p class="message">
                        ${data.mess}
                    </p>
                    <span class="message-details text-dark">
                        <i class="fas fa-calendar-alt ml-1"></i>
                        التاريخ:
                        ${data.date}
                        <i class="fas fa-clock ml-1"></i>
                        الوقت:
                        ${data.time}
                    </span>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>`;
          return item;
        }
      }
    );
  }
}
function inboxMessage(title, sender, message, date, email, phone) {
  var item = `<table class="table">
                <tr>
                    <th>عنوان الرسالة:</th>
                    <td>${title}</td>
                </tr>
                <tr>
                    <th>المرسل:</th>
                    <td>${sender}</td>
                </tr>
                <tr>
                    <th>البريد الإلكتروني:</th>
                    <td>${email}</td>
                </tr>
                <tr>
                    <th>رقم الهاتف:</th>
                    <td>${phone}</td>
                </tr>
                <tr>
                    <th>الرسالة:</th>
                    <td>${message}</td>
                </tr>
                <tr>
                    <th>تاريخ وموعد الرسالة:</th>
                    <td>${date}</td>
                </tr>
              </table>`;
  return item;
}
// Function to handle notifications (already exists in login.php, duplicating here just for context, not modifying)
function notiModal(message) {
    $("#notiBody").html(message);
    $("#notiModal").modal("show");
}

AOS.init();

$(function() {
    var headerHeight = $(".header").height(),
        footerHeight = $("footer").height(),
        windowHeight = $(window).height(),
        newHeight = windowHeight - (headerHeight + footerHeight) - 60;
    $("#loginContent").css("min-height", newHeight);

    // الكود الخاص بإعادة التوجيه إلى m.wastetco.com تم حذفه هنا.
    // تأكد أنك لم تعد بحاجة لأي منطق تفريعي للدومين المحمول.

    $("#logout").click(function () {
        $.post("../inc/run.php", { target: "logout" }, () => {
            window.location.href = "../login.php";
        });
    });
});

function setCookie(cname, cvalue, exdays) {
  var d = new Date();
  d.setTime(d.getTime() + exdays * 24 * 60 * 60 * 1000);
  var expires = "expires=" + d.toUTCString();
  document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}
function getCookie(cname) {
  var name = cname + "=";
  var decodedCookie = decodeURIComponent(document.cookie);
  var ca = decodedCookie.split(";");
  for (var i = 0; i < ca.length; i++) {
    var c = ca[i];
    while (c.charAt(0) == " ") {
      c = c.substring(1);
    }
    if (c.indexOf(name) == 0) {
      return c.substring(name.length, c.length);
    }
  }
  return "";
}
function financeProcessModal(code, name, phone, type, value, reason, date, hour) {
  var item = `<table class="table">
                <tr>
                    <th>كود العملية:</th>
                    <td>${code}</td>
                </tr>
                <tr>
                    <th>إسم العميل:</th>
                    <td>${name}</td>
                </tr>
                <tr>
                    <th>رقم الهاتف:</th>
                    <td>${phone}</td>
                </tr>
                <tr>
                    <th>نوع العملية:</th>
                    <td>${type}</td>
                </tr>
                <tr>
                    <th>قيمة العملية:</th>
                    <td>${value}</td>
                </tr>
                <tr>
                    <th>سبب العملية:</th>
                    <td>${reason}</td>
                </tr>
                <tr>
                    <th>تاريخ وموعد العملية:</th>
                    <td>${date}   -   ${hour}</td>
                </tr>
              </table>`;
  return item;
}
if (getCookie("accept_cookie") == "") {
  $("body").append(`
            <div class="cookie">
                <div class="container">
                    يستخدم الموقع ملفات تعريف الإرتباط لتوفير أفضل تجربة إستخدام ممكنة
                    <button class="btn btn-success btn-sm ml-2 mr-2" id="acceptCookie" type="button">أوافق</button>
                    <button class="btn btn-danger btn-sm ml-2 mr-2" id="refuseCookie" type="button">لا أوافق</button>
                    (لن تظهر هذه الرسالة مجددا بمجرد موافقتك عليها لمرة واحدة فقط)
                </div>
            </div>`);

  $("#refuseCookie").click(() => {
    if (confirm("هل أنت متأكد من قرارك بعدم الموافقة على إستخدامنا ملفات تعريف الإرتباط وتريد مغادرة الموقع؟")) {
      close();
    }
  });
  $("#acceptCookie").click(function(){
    setCookie("accept_cookie", "yes", 1000);
    $(this).parent().parent().slideDown(500).remove();
  })
}

