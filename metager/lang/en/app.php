<?php
return [
    'head' => [
        '1' => 'MetaGer Apps',
        '2' => 'MetaGer App',
        '3' => 'MetaGer Maps App',
        '4' => 'Installation',
    ],
    'disclaimer' => [
        '1' => 'At this time we only have an Android version of our App.',
    ],
    'metager' => [
        '1' => 'This App brings the full Metager power to your smartphone. Search the web with one touch while preserving your privacy.',
        '2' => 'There are two ways to get our App: install via the Google Playstore or (better for your privacy) get it directly from our server.',
        'playstore' => 'Google Playstore',
        'fdroid' => 'F-Droid Store',
        'manuell' => 'Manual Installation',
    ],
    'maps' => [
        '1' => 'This App provides a native integration of <a href="https://maps.metager.de" target="_blank">MetaGer Maps</a> (powered by <a href="https://www.openstreetmap.de/" target="_blank">Openstreetmap</a>) on your mobile Android device.',
        '2' => 'Therefore, the route planner and the navigation service is running very fast on your smartphone. The app is faster compared against the use in a mobile web browser. And there are some more advantages- check it out!',
        '3' => 'The APK for manual installation is around 4x the size of the playstore installation (~250MB) because it contains libraries for all common CPU architectures. The integrated updater will know your devices CPU architecture and will install the correct (small) version of the app on the first update. If you know your devices architecture yourself you can also <a href="https://gitlab.metager.de/metagermaps/android/-/releases/permalink/latest" target="_blank">directly install the small package</a>.',
        '4' => 'After the first start you will be asked for the following permissions:',
        'list' => [
            '1' => 'Access to positioning data => With GPS activated we can provide better search results. With this you get access to the step-by-step navigation. <b> Of course, we don\'t store any of your data and we don\'t give any of your data to third persons.</b>',
            '2' => 'The APK for manual installation has an integrated updater. For the updater to work the app will ask for permission to post notifications in order to notify you of an available update and uses the Android permission REQUEST_INSTALL_PACKAGES so it can ask you to install the app update',
        ],
    ]
];
