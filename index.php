<!DOCTYPE html>
  <html>
  <head>
    <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="index.css" />
    <title>Facedrive</title>
    <script src="jquery.js"></script>
  </head>
<body>
<div id="AppContainer"><img class="logo" src="images/fblogo.png">
<div id="fb-root"></div>
<script>
var app = {
  db: {
    files:[]
  },
  fs:[]
};

  window.fbAsyncInit = function() {
  FB.init({
    appId      : '167071830164878', // Production
    channelUrl : '//facedrive.heroku.com/channel.html', // Channel File
    status     : true, // check login status
    cookie     : true, // enable cookies to allow the server to access the session
    xfbml      : true  // parse XFBML
  });

  // Here we subscribe to the auth.authResponseChange JavaScript event. This event is fired
  // for any authentication related change, such as login, logout or session refresh. This means that
  // whenever someone who was previously logged out tries to log in again, the correct case below 
  // will be handled. 
  FB.Event.subscribe('auth.authResponseChange', function(response) {
    // Here we specify what we do with the response anytime this event occurs. 
    if (response.status === 'connected') {
      // The response object is returned with a status field that lets the app know the current
      // login status of the person. In this case, we're handling the situation where they 
      // have logged in to the app.
      testAPI();
    } else if (response.status === 'not_authorized') {
      // In this case, the person is logged into Facebook, but not into the app, so we call
      // FB.login() to prompt them to do so. 
      // In real-life usage, you wouldn't want to immediately prompt someone to login 
      // like this, for two reasons:
      // (1) JavaScript created popup windows are blocked by most browsers unless they 
      // result from direct interaction from people using the app (such as a mouse click)
      // (2) it is a bad experience to be continually prompted to login upon page load.
      FB.login(function(response){alert(response);},{scope:'read_mailbox,user_groups,friends_groups,read_page_mailboxes',perms:'read_mailbox,user_groups,friends_groups,read_page_mailboxes'});
    } else {
      // In this case, the person is not logged into Facebook, so we call the login() 
      // function to prompt them to do so. Note that at this stage there is no indication
      // of whether they are logged into the app. If they aren't then they'll see the Login
      // dialog right after they log in to Facebook. 
      // The same caveats as above apply to the FB.login() call here.
      FB.login(function(response){alert(response);},{scope: 'read_mailbox,user_groups,friends_groups,read_page_mailboxes',perms:'read_mailbox,user_groups,friends_groups,read_page_mailboxes'});
    }
  });
  };

  // Load the SDK asynchronously
  (function(d){
   var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
   if (d.getElementById(id)) {return;}
   js = d.createElement('script'); js.id = id; js.async = true;
   js.src = "//connect.facebook.net/en_US/all.js";
   ref.parentNode.insertBefore(js, ref);
  }(document));

  // Here we run a very simple test of the Graph API after login is successful. 
  // This testAPI() function is only called in those cases. 
  function testAPI() {
    console.log('Welcome!  Fetching your information.... ');
    FB.api('/me?fields=inbox',getInbox);
    FB.api('/me?fields=groups',getGroups);
    FB.api('/me', function(response) {
      console.log('Good to see you, ' + response.name + '.');
    });

  }

function getGroups(obj)
{
  console.debug(obj);
  for(var i in obj.groups.data)
  {
    
  }
}

function getInbox(obj)
{
  for(var i in obj.inbox.data)
  {
    var arrInbox = obj.inbox.data[i];
    var arrTo = arrInbox.to.data;
    if(typeof arrInbox.comments != 'undefined')
    {
      for(var j in arrInbox.comments.data)
      {
        var arrComments = arrInbox.comments.data[j];
        var urls = getUrls(arrComments.message);
        if(urls.length>0)
        {
          NoSalgasAsi(arrComments.created_time);
          app.db.files.push({
            type: "inbox",
            fileType: getFileType(urls[0]),
            fileName: getFileName(urls[0]),
            from: arrComments.from,
            to: arrTo,
            file: urls[0],
            date: arrComments.created_time
          });
        }
      }
    }
  }
  drawFS('from.name');
}

function NoSalgasAsi(datelo)
{
  
}

function setFS(field)
{
  var exit = {};
  for(var i in app.db.files)
  {
    var record = app.db.files[i];
    var arrFields = field.split('.');
    if(Array.isArray(record[arrFields[0]]))
    {
      for(var j in record[arrFields[0]])
      {
        var valField = record[arrFields[0]][i];
        if(arrFields.length>1)
        {
          if(typeof exit[valField[arrFields[1]]] == 'undefined')
          {
            exit[valField[arrFields[1]]] = [];
          }
          exit[valField[arrFields[1]]].push(record);
        }
        else
        {
          if(typeof exit[valField] == 'undefined')
          {
            exit[valField] = [];
          }
          exit[valField].push(record);
        }
      }
    }
    else if(record[arrFields[0]] instanceof Object)
    {

      if(typeof exit[record[arrFields[0]][arrFields[1]]] == 'undefined')
      {
        exit[record[arrFields[0]][arrFields[1]]] = [];
      }
      exit[record[arrFields[0]][arrFields[1]]].push(record);
    }
    else
    {
      if(typeof exit[record[arrFields[0]]] == 'undefined')
      {
        exit[record[arrFields[0]]] = [];
      }
      exit[record[arrFields[0]]].push(record);
    }
  }
  return exit;
}

function drawFS(field)
{
  var exit = '';
  var nodes = setFS(field);
  for(var i in nodes)
  {
    exit += '<div class="directory">';
    exit +=   '<div class="directoryTitle">';
    exit +=     i;
    exit +=   '</div>';
    exit +=   '<div class="directoryNodes">';
  
    for(var j in nodes[i])
    {
      var item = nodes[i][j];
      exit +=   '<div class="item '+item.type+' '+item.fileType+'">';
      exit +=      '<div class="itemImage">';
      exit +=         '<img class="imgObj" src="http://graph.facebook.com/'+item.from.id+'/picture?type=square">';
      exit +=         '<img class="imgLogo" src="./images/'+item.fileType+'.png">';
      exit +=      '</div>';
      exit +=      '<div class="itemTitle">';
      exit +=         '<a target="_blank" href="'+item.file+'">'+item.fileName+'</a>';
      exit +=      '</div>';
      exit +=      '<div class="itemDate">';
      exit +=         item.date;
      exit +=      '</div>';
      exit +=   '</div>';
    }
    exit +=   '</div>';
    exit += '</div>';
  }
  $('#files').html(exit);
}

function getUrls(data)
{
  var match,exit = [];
  var regex = new RegExp(/^(ftp|https?):\/\/(\w+:{0,1}\w*@)?((?![^\/]+\/(?:ftp|https?):)\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?$/);
  if((match=regex.exec(data))!=null)
  {
    exit.push(match[0]);
  }
  return exit;
}

function getFileName(url)
{
  var exit = url;
  var arrUrl = url.split('//');
  var arrParts = arrUrl[1].split('/');
  exit = arrParts[0];
  if(arrParts.length>1)
  {
    exit = arrParts[0]+'/.../'+arrParts.pop();
  }
  return exit;
}

function getFileType(url)
{
  var exit = 'web';
  var arrUrl = url.split('//');
  var arrParts = arrUrl[1].split('/');
  var arr = {
    domain:arrParts[0],
    page:''
  };
  if(arrParts.length>1)
  {
    arr.page = arrParts[arrParts.length-1];
  }
  if(arr.domain.indexOf('youtube.com')!=-1)
  {
    exit = 'video';
  }
  else if(arr.domain.indexOf('facebook.com')!=-1)
  {
    exit = 'facebook';
  }
  else if(arr.domain.indexOf('slideshare.com')!=-1)
  {
    exit = 'slides';
  }
  else if(arr.domain.indexOf('vimeo.com')!=-1)
  {
    exit = 'video';
  }
  else if(arr.domain.indexOf('flickr.com')!=-1)
  {
    exit = 'photo';
  }
  else if(arr.page != '')
  {

    var arrExt = arr.page.split('.');
    if(arrExt.length > 1)
    {
      var ext = arrExt.pop();
      if(ext=='pdf')
      {
        exit = 'pdf';
      }
      else if(ext=='xls')
      {
        exit = 'spreadsheet';
      }
      else if(ext=='xlsx')
      {
        exit = 'spreadsheet';
      }
      else if(ext=='doc')
      {
        exit = 'document';
      }
      else if(ext=='docx')
      {
        exit = 'document';
      }
      else if(ext=='ppt')
      {
        exit = 'slides';
      }
      else if(ext=='pptx')
      {
        exit = 'slides';
      }
      else if(ext=='jpg')
      {
        exit = 'photo';
      }
      else if(ext=='jpeg')
      {
        exit = 'photo';
      }
      else if(ext=='png')
      {
        exit = 'photo';
      }
      else if(ext=='gif')
      {
        exit = 'photo';
      }
      else if(ext=='avi')
      {
        exit = 'video';
      }
      else if(ext=='mp4')
      {
        exit = 'video';
      }
      else if(ext=='mov')
      {
        exit = 'video';
      }
      else if(ext=='mp3')
      {
        exit = 'audio';
      }
      else if(ext=='wav')
      {
        exit = 'audio';
      }
      else if(ext=='ogg')
      {
        exit = 'audio';
      }
    }
  }
  return exit;
}

</script>

<!--
  Below we include the Login Button social plugin. This button uses the JavaScript SDK to
  present a graphical Login button that triggers the FB.login() function when clicked.

  Learn more about options for the login button plugin:
  /docs/reference/plugins/login/ -->

<fb:login-button scope="read_mailbox,user_groups,friends_groups,read_page_mailboxes" show-faces="true" width="300" max-rows="2"></fb:login-button>

<div id="files"></div>
</div>
    
</body>
</html>