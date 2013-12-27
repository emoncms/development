# How to create an emoncms module

In this tutorial we will create a simple custom display module for emoncms that shows a readout of current home power use and how many kwh's have been used today.

![My Electric](images/myelectric.png)

## 1) Create a folder for your module

When you open the emoncms directory you will see a folder called Modules, lets call our new module “myelectric”. Create a folder within the Modules folder called “myelectric”.

![Modules Folder](images/modulesfolder.png)

## 2) The Controller

The most important script (file) that your module needs is a script called the controller. When you go to *http://emoncms.org/myelectric* the module controller is what deals with that request. Create a file called *myelectric_controller.php* in the *myelectric* folder and copy the following code in there:

    <?php

      // no direct access
      defined('EMONCMS_EXEC') or die('Restricted access');

      function myelectric_controller()
      {
        global $session,$route;
        $result = false;

        if ($route->action == "view") $result = "<h2>Hello World</h2>";

        return array('content'=>$result);
      }

Try it out, navigate to http://your-ip-address/emoncms/myelectric/view in your browser:

![Hello World](images/helloworld.png)

## Important concept: The front controller

When you make a request to emoncms such as:

    http://emoncms.org/myelectric/view

it doesnt actually go to a folder called myelectric and a file called view as you might expect. What actually happens is that: 

     http://emoncms.org/myelectric/view 

is first converted by a file called .htaccess into:

    http://emoncms.org?q=myelectric/view

"myelectric/view" is now the value of the URL property "q" and this argument is passed to index.php wich you will see in the main emoncms folder. 


## 3) The view

Its best not to build the content (view) in the controller itself as we've done in the simple hello world example above instead we create another file for this which we then load from the controller.

Change the line in myelectric_controller.php:

    if ($route->action == "view") $result = "<h2>Hello World</h2>";

to 

    if ($route->action == "view") $result = view("Modules/myelectric/myelectric_view.php",array());
    
and create a file called myelectric_view.php in the myelectric module folder, type some html in there:

    <h2>Hello World</h2>
    <p>Im now serving this from myelectric_view.php</p>

Try it out, navigate to http://your-ip-address/emoncms/myelectric/view in your browser, you should see hello world again.

### myelectric_view.php example code:

    <!-- bring in the emoncms path variable which tells this script what the base URL of emoncms is -->
    <?php global $path; ?>

    <!-- feed.js is the feed api helper library, it gives us nice functions to use within our program that
    calls the feed API on the server via AJAX. -->
    <script language="javascript" type="text/javascript" src="<?php echo $path; ?>Modules/feed/feed.js"></script>

    <!-- defenition of the style/look of the elements on our page (CSS stylesheet) -->
    <style>

      .electric-title {
        font-weight:bold; 
        font-size:22px; 
        color:#aaa; 
        padding-top:50px
      }
      
      .power-value {
        font-weight:bold; 
        font-size:100px; 
        color:#0699fa; 
        padding-top:45px;
      }
      
      .kwh-value {
        font-weight:normal; 
        font-size:22px; 
        color:#0699fa; 
        padding-top:45px;
      }
      
    </style>

    <!-- The three elements: title, power value and kwhd value that makes up our page -->
    <!-- margin: 0px auto; max-width:320px; aligns the elements to the middle of the page -->
    <div style="margin: 0px auto; max-width:320px;">
        <div class="electric-title">POWER NOW:</div>
        <div class="power-value"><span id="power"></span>W</div>
        <div class="kwh-value">USE TODAY: <b><span id="kwhd"></span> kWh</b></div>
    </div>

    <script>

      // The feed api library requires the emoncms path
      var path = "<?php echo $path; ?>"
      
      // Set the background color to dark grey - looks nice on a mobile.
      $("body").css('background-color','#222');
      
      update();

      // Set interval is a way of scheduling an periodic call to a function
      // which we can then use to fetch the latest power value and update the page.
      // update interval is set to 5 seconds (5000ms)
      setInterval(update,5000);
      
      function update()
      {
        // Get latest feed values from the server (this returns the equivalent of what you see on the feed/list page)
        var feeds = feed.list_by_id();    
        
        // Update the elements on the page with the latest power and energy values.
        $("#power").html(feeds[1063]);
        $("#kwhd").html(feeds[1064].toFixed(1));
      }
      
    </script>




