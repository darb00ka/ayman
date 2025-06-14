$.post("../inc/run.php", { target: "check-noti" }, (data) => {
  var data = JSON.parse(data);
  if (data.error) {
    notiModal(`<i class="fas fa-times text-danger ml-2"</i>${data.message}`);
  } else {
    if (data.num > 0) {
      $("#notifications-link").children("div").remove()
      $("#notifications-link").append(
        `<div class="d-inline-block bg-danger rounded-circle text-center" style="height: 25px;width: 25px;line-height: 25px;">${data.num}</div>`
      );
      $("#notifications-link").attr("href", `notifications.php?num=${data.num}`);
    }
  }
});

setInterval(() => {
  $.post("../inc/run.php", { target: "check-noti" }, (data) => {
    var data = JSON.parse(data);
    if (data.error) {
      if(!data.check){
        window.location.href="../login";
      }else{
        notiModal(`<i class="fas fa-times text-danger ml-2"</i>${data.message}`);
      }
    } else {
      if (data.num > 0) {
        $("#notifications-link").children("div").remove()
        $("#notifications-link").append(
          `<div class="d-inline-block bg-danger rounded-circle text-center" style="height: 25px;width: 25px;line-height: 25px;">${data.num}</div>`
        );
        $("#notifications-link").attr("href", `notifications.php?num=${data.num}`);
      }
    }
  });
}, 30000);
