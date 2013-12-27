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




