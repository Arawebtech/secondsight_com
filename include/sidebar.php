<style>  
.desktop-model{display:block; bottom: 200px; position: fixed; text-align: center; width: 335px; z-index: 99999; height: 50px; right:-146px; transform: rotate(-90deg)}
.mobile-model{display:none;}

.desktop-sticky-btn-icon{background-color: #fff; color: #000; padding: 10px; border-right: 1px solid #000;list-style: none;}
.desktop-sticky-btn-contact{background-color: #fff; color: #000; padding: 3px; font-size: 20px;}
    
@media only screen and (max-width: 768px) 
{
    .desktop-model{display:none;}   
    .mobile-model{display: block;bottom: 0; position: fixed; text-align: center; width: 100%; z-index: 99999;height: 50px;}
    
    .mobile-sticky-btn-icon{background-color: #fff; color: #000; padding: 15px;list-style: none;}
    .mobile-sticky-btn-contact{background-color: #fff; color: #000; padding: 8px; font-size: 20px;}
}
</style>

<div class="desktop-model">
  <ul id="desktop-left" style="display: inline-flex; width:100%">
      <li class="desktop-sticky-btn-icon" style="width:50%">
          <a href="https://wa.me/9716517463" target="_blank">
              <i class="fab fa-whatsapp" style="font-size:20px; color:#0cbb6a; transform: rotate(90deg)"></i>
              <span style="color:#000">WhatsApp Us</span>
          </a>
      </li>
      <!--<li class="desktop-sticky-btn-contact" style="width:55%">
          <?php //include('includes/popup-right.php'); ?>
      </li>-->
      <li class="desktop-sticky-btn-icon" style="width:50%">
          <a href="tel:9716517463" target="_blank">
              <i class="fa fa-phone" style="font-size:20px; color:#11d379; transform: rotate(90deg)"></i>
              <span style="color:#000">Contact Us</span>
          </a>
      </li>
  </ul>
</div>


<div class="mobile-model">

  <ul id="mobile-left" style="display: inline-flex; width:100%; padding-left:0">
      <li class="mobile-sticky-btn-icon" style="width:50%">
          <a href="https://wa.me/9716517463" target="_blank">
              <i class="fab fa-whatsapp" style="font-size:20px; color:#11d379"></i>
              <span style="color:#000"> WhatsApp Us</span>
          </a>
      </li>
      <!--<li class="mobile-sticky-btn-contact" style="width:55%">-->
      <!--    <?php include('includes/popup-bottom.php'); ?>-->
      <!--</li>-->
      <li class="mobile-sticky-btn-icon" style="width:50%">
          <a href="tel:+9716517463" target="_blank">
              <i class="fa fa-phone" style="font-size:20px; color:#11d379; transform: scaleX(-1);"></i>
              <span style="color:#000"> Contact Us</span>
          </a>
      </li>
  </ul>
</div>


<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>

<script>
//for desktop
$(document).ready(function(){
  $("#desktop-right").hide();
  $("#desktop-left").show();
  $("#desktop-right").click(function(){
    $("#desktop-right").hide();
    $("#desktop-left").show();
  });
  $("#desktop-left-icon").click(function(){
    $("#desktop-left").hide();
    $("#desktop-right").show();
  });
});


//for mobile
$(document).ready(function(){
  $("#mobile-right").hide();
  $("#mobile-left").show();
  $("#mobile-right").click(function(){
    $("#mobile-right").hide();
    $("#mobile-left").show();
  });
  $("#mobile-left-icon").click(function(){
    $("#mobile-left").hide();
    $("#mobile-right").show();
  });
});
</script>

<!--chatboat-->




