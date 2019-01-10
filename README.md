# release-manager
Orbisius Release Manager allows WordPress developers/designers to easily release their plugins (soon and themes) at WordPress.org

= What this tool does =
The tool simplifies the job of the WordPress developer by allowing him/her to release a version using a single click.
The tools will tell you if a plugin needs to be tested with the latest WordPress version (by checking the readme file)
Also it warns you if there are any uncommited changes, the stable tag doesn't match the current version etc.
It also checks if you have a change log for a given version and much more.
When all conditions are met the Push Release button will appear.
Shows the release dir in a nice textbox for easy copy/paste.

Note: The tool is intended to be used on the local development machine that's not accessible from the internet.

= Configuration =
* Download / Clone the repo in a folder that's accessible from within your browser 
e.g. htdocs/release-manager/

* Create conf/config.custom.php using the conf/sample.config.custom.php and define your WordPress.org credentials and which folders to be scanned.

* Access it 
Your plugins should be listed here.
http://localhost/release-manager/


= How to Contribute =

If you have a suggestion or feature request submit it here: 
https://github.com/orbisius/release-manager/issues

Also you can clone the repo and do a pull request.

= Todo =
- Show plugins in the sidebar for quick access
- Collapse all and open when needed?

= Author =

Svetoslav (Slavi) Marinov is an entrepreneur / developer who likes creating cool and useful tools.

Site: http://orbisius.com | http://qSandbox.com
Product page: https://orbisius.com/products/orbisius-release-manager
Twitter: http://twitter.com/lordspace | http://twitter.com/orbisius

If you ever need a web app or a custom WordPress plugin done feel free to submit a free quote request at http://club.orbisius.com/free-quote/
