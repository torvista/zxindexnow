# ZX IndexNow
IndexNow plugin for Zen Cart
    <h3 id="install"><strong>Installing</strong></h3>
    <p><strong><font color="red">ZX IndexNow Plugin is designed to work with Zen Cart 2.1.0
        (latest version at the time of release). It should work with all releases 1.5.8 onwards but hasn't been tested. Older versions are NOT supported, although might work.</font></strong></p>
    <p><strong>Before you proceed, make a full backup of your site's files AND database. Installation is done at your
        own risk.</strong></p>
    <p>Connect to your server via FTP. Upload the contents of zc_plugins to your zc_plugins directory (or simply upload the entire zc_plugins directory to your store root). Be careful, the store root directory on
        the server is where your store is installed, not necessarily domain root.</p>
    <p>Go to your admin section and use Modules->Plugin Manager to install the plugin.</p>
    <p>Verify that a new text file has been created in your store root - it will be a 30-character txt file which is used as your key.</p>
    <hr>
    <h3 id="usage"><strong>Usage</strong></h3>
    <p>This plugin is fully automated once installed. There are no options to choose from or settings to adjust.</p>
    <p>Adding a new product or category, or editing an existing product or category will automatically trigger a submission to Bing.</p>
    <p>Only the URL is submitted, nothing else. You're are only notifying the search engine that this URL has some changes and should be (re)indexed. Submission is directly to Bing and no other search engine.</p>
    <p><strong>IMPORTANT:</strong> if for any reason you don't want to submit a specific product or category, you will have to uninstall the plugin before adding or updating the category or product.</p>
    <p>If you want to submit the URL to all supported search engines (currently Microsoft Bing, Naver, Seznam.cz, Yandex, Yep), you can change the endpoint in the observer file (auto.indexnow.php) to https://api.indexnow.org</p>
    <hr>
