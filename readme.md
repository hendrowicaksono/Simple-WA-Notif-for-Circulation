# (very) Simple circulation Notification by Whatsapp

## INTRO
- This plugin is for sending circulation notification using whatsapp in [SLiMS](https://slims.web.id).
- Using API service from [Whacenter](https://whacenter.com/). So you should register first to use the [Whacenter](https://whacenter.com/) service. Then you can continue register your whatsapp number and get the device ID.

## How to use the plugin

- Download this plugin and put in SLiMS plugins directory.
    - You can download this repo, and do the "composer update".
    - Or you can use the build package from [release page](https://github.com/hendrowicaksono/Simple-WA-Notif-for-Circulation/releases).
- Go into the folder, edit `circ_notif_bywa.plugin.php` file, update line: `$ccnw['device_id'] = 'put_your_device_id_here';`, change `put_your_device_id_here` text with your [Whacenter](https://whacenter.com/) device id.
- Activate the plugin: `System` -> `Plugin` -> `Simple Circulation Notification using Whatsapp`.
- Now try the circulation process. If anything is going well, then the user who borrows or returns the library collection will get notification via Whatsapp. BUT DONT FORGET, make sure the Phone number is membership data is filled with member's whatsapp number.

## Warning
- This plugin is designed for low scale circulation activities. If your library has very high circulation traffic, then consider to use implementation using message broker instead.

## Last but not least
[Support us](https://saweria.co/hendrowicaksono) building SLiMS ecosystem better, maintained, and more complete.
