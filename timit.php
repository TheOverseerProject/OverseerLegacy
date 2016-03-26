<script src="http://code.jquery.com/jquery-latest.min.js" type="text/javascript"></script>
<script>
var countdown = setInterval(function () {
    var minutes = parseInt($("span.c1").html());
    var seconds = parseInt($("span.c2").html());
    var encounters = $("span.c3").html();
    seconds--;
    if (seconds == -1) {
        seconds = 59;
        minutes--;
    }
    if (minutes == -1) {
      minutes = 19;
      seconds = 59;
      if (encounters < 100) {
        encounters++;
      }
    }
    $("span.c1").html(("0"+minutes).slice(-2));
    $("span.c2").html(("0"+seconds).slice(-2));
    $("span.c3").html(encounters);
}, 1000);

var countit = setInterval(function () {
    var minutes = parseInt($("span.d1").html());
    var seconds = parseInt($("span.d2").html());
    var encounters = $("span.d3").html();
    seconds--;
    if (seconds == -1) {
        seconds = 59;
        minutes--;
    }
    if (minutes == -1) {
      minutes = 19;
      seconds = 59;
      if (encounters < 100) {
        encounters++;
      }
    }
    $("span.d1").html(("0"+minutes).slice(-2));
    $("span.d2").html(("0"+seconds).slice(-2));
    $("span.d3").html(encounters);
}, 1000);
</script>
<span class="c3">100</span>
</br>
<span class="c1">00</span>:<span class="c2">01</span>
</br>
</br>
<span class="d3">100</span>
</br>
<span class="d1">00</span>:<span class="d2">01</span>