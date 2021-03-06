importScripts("themes/ddb/javascripts/vendor/workbox/workbox-v5.1.3/workbox-sw.js");

if (workbox) {

  workbox.setConfig({
    // debug: true,
    clientsClaim: true,
    skipWaiting: true
  });

  const FALLBACK_IMAGE_URL = self.registration.scope + 'themes/ddb/images/offline.png';

  workbox.precaching.precacheAndRoute([
    {"revision":null,"url":FALLBACK_IMAGE_URL},
    {"revision":null,"url":self.registration.scope + "manifest"},
    {"revision":"00000000000000000000000000000002","url":self.registration.scope},
    {"revision":"00000000000000000000000000000001","url":self.registration.scope + "exhibits/colorpalettesjs"},
    {"revision":null,"url":"themes/ddb/css/spa.min.css?v=20201130171222"},
    {"revision":null,"url":"themes/ddb/javascripts/bundle.min.js?v=20201130171214"},
    {"revision":"1b79051db915bd98eff66eb565a797df","url":"themes/ddb/javascripts/vendor/jwplayer/jwplayer.html5.js"},
    {"revision":"b3877f5f9d80e807dd2b97a5a39841d1","url":"themes/ddb/images/favicon.ico"},
    {"revision":"76d200f5961f67e162079ca16672b367","url":"themes/ddb/images/menu_icon_3d_bg.svg"},
    {"revision":"1fdd3338bef7419d391db37d120da9ea","url":"themes/ddb/images/menu_icon_3d.svg"},
    {"revision":"f67129681fa2203f5a60b27bfc4ed776","url":"themes/ddb/images/menu_icon_audio.svg"},
    {"revision":"40963ed981f66716a9884c4c8ac9ce96","url":"themes/ddb/images/menu_icon_home.svg"},
    {"revision":"37628e6081ee4239399ef4f17f6e4808","url":"themes/ddb/images/menu_icon_img.svg"},
    {"revision":"69762b8f092b5c0d924560b78a255a19","url":"themes/ddb/images/menu_icon_legal.svg"},
    {"revision":"fdbcdcce8375c6e67b06c93a0af57d49","url":"themes/ddb/images/menu_icon_privacy.svg"},
    {"revision":"21f9b348c00d3382a4ebe79d6fe4ff46","url":"themes/ddb/images/menu_icon_slider.svg"},
    {"revision":"6aa469dafec6c98a9c8657de3222a563","url":"themes/ddb/images/menu_icon_team.svg"},
    {"revision":"2a53099666b46ea8f291d2b170cad41f","url":"themes/ddb/images/menu_icon_text.svg"},
    {"revision":"a5c1ad27fcf5213fc2862cfc73d97394","url":"themes/ddb/images/menu_icon_video.svg"},
    {"revision":"0f6edf7f2376a231e8e1ac6ebc6a30e8","url":"themes/ddb/images/1_dbb_siegel_de_service_rot_invert.png"},
    {"revision":"3da96c0d4e10252e6f4c139043d1e8ad","url":"themes/ddb/images/2_ddb_api_dt_rot.png"},
    {"revision":"0c3ec37df563ba653dde9a85ddc39ca7","url":"themes/ddb/images/3_DDB_Logo_2_s_pos_RGB_R_96dpi.png"},
    {"revision":"18de4e98a3743a51dba12ea467cb3884","url":"themes/ddb/images/ddb-studio-logo-small.svg"},
    {"revision":"c3dc6b83c061d69a85a83c40070f85f6","url":"themes/ddb/images/ddb-studio-logo-small-inverse.svg"},
    {"revision":"548f76ef877b6181e4b8abe96d783236","url":"themes/ddb/images/ddb-studio-logo-small.png"},
    {"revision":"59eac59d107607779ca2751d76e9c03c","url":"themes/ddb/images/logo.png"},
    {"revision":"a77ff649a3cce562e053d16e7382eded","url":"themes/ddb/images/licenses.png"},
    {"revision":"a6e1c6a64d9b88553268190a832309d6","url":"themes/ddb/images/licenses_dark.png"},
    {"revision":"0f2802a735489b9cffe9ee72a73c48b1","url":"themes/ddb/images/icons/icon_facebook_dark.svg"},
    {"revision":"1d4d006c7621f24f764e5d6f93273d91","url":"themes/ddb/images/icons/icon_facebook.svg"},
    {"revision":"3a66d71fbbf88d6de8e9914af61fdd72","url":"themes/ddb/images/icons/icon_next_page_left.svg"},
    {"revision":"1c75007547cba6835fb810dd47b6c6a4","url":"themes/ddb/images/icons/icon_next_page_right.svg"},
    {"revision":"e90bc7744a9e37feb3f9bfde015a4f18","url":"themes/ddb/images/icons/icon_pinterest_dark.svg"},
    {"revision":"a828b13d942e5e192a94a407df487bb2","url":"themes/ddb/images/icons/icon_pinterest.svg"},
    {"revision":"1aa077c9bbcbf478fdbd0a0752d7f098","url":"themes/ddb/images/icons/icon_tumblr_dark.svg"},
    {"revision":"cfb5c46cffb2e38323d9aa3199ced8e4","url":"themes/ddb/images/icons/icon_tumblr.svg"},
    {"revision":"fb59183613f061df8eadcbf75e88e06b","url":"themes/ddb/images/icons/icon_twitter_dark.svg"},
    {"revision":"72f6273a3df2632141b8c5b1c64e95ce","url":"themes/ddb/images/icons/icon_twitter.svg"},
    {"revision":"f0b4984a9026f1dcf99d543dfb8a7fc2","url":"themes/ddb/images/icons/x.svg"},
    {"revision":"2c8033cb6b95c5702c6ed8fb7a4ce18d","url":"themes/ddb/images/icons/zoom/icon_zoom-hint_move_drag.svg"},
    {"revision":"632b4b7dc78f9d68027ea6fa487735d3","url":"themes/ddb/images/icons/zoom/icon_zoom-hint_move_keys.svg"},
    {"revision":"75cef1ab5b513dbfd46e18b0bad3c1e3","url":"themes/ddb/images/icons/zoom/icon_zoom-hint_move_mouse.svg"},
    {"revision":"d5e9b9788dfb5e35c910ac20ffdc0a5b","url":"themes/ddb/images/icons/zoom/icon_zoom-hint_quit_click.svg"},
    {"revision":"351a470ee5c6fc18385928bd786dec79","url":"themes/ddb/images/icons/zoom/icon_zoom-hint_quit_keys.svg"},
    {"revision":"391baa0f7988dd3b47c64959ac8663f2","url":"themes/ddb/images/icons/zoom/icon_zoom-hint_quit_mouse.svg"},
    {"revision":"9d5a8e1930371627a810d6ebfab00fb1","url":"themes/ddb/images/icons/zoom/icon_zoom-hint_zoom_keys.svg"},
    {"revision":"8be7badc10a0b3627fd223b5ea5c77b7","url":"themes/ddb/images/icons/zoom/icon_zoom-hint_zoom_mouse.svg"},
    {"revision":"b5f3f48fdd33a84287e0970301d52acc","url":"themes/ddb/images/icons/zoom/icon_zoom-hint_zoom_pinch.svg"},
    {"revision":"a0ec15b454c5d1290c3e3d3aa75b4473","url":"plugins/X3d/views/shared/javascripts/x3dom.js"},
  ]);

  workbox.routing.registerRoute(
    /(.*)\.(?:png|gif|jpg|svg|ico|webp)/,
    new workbox.strategies.CacheFirst({
      cacheName: "images",
      plugins: [
        new workbox.expiration.ExpirationPlugin({
          maxEntries: 500,
          maxAgeSeconds: 365 * 24 * 60 * 60, // 1 Year
        })
      ]
    })
  );

  workbox.routing.registerRoute(
    /(.*)\.(?:eot|woff2|woff|woff2|ttf)/,
    new workbox.strategies.CacheFirst({
      cacheName: "fonts",
      plugins: [
        new workbox.expiration.ExpirationPlugin({
          maxEntries: 500,
          maxAgeSeconds: 365 * 24 * 60 * 60, // 1 Year
        })
      ]
    })
  );

  workbox.routing.registerRoute(
    /(.*)\.(?:mp3|ogg)/,
    new workbox.strategies.CacheFirst({
      cacheName: 'audio',
      plugins: [
        new workbox.cacheableResponse.CacheableResponsePlugin({statuses: [200]}),
        new workbox.rangeRequests.RangeRequestsPlugin()
      ]
    })
  );

  workbox.routing.registerRoute(
    /(.*)piwik\.(?:js|php)/,
    new workbox.strategies.NetworkOnly({
      cacheName: 'network',
      plugins: []
    })
  );

  workbox.routing.setDefaultHandler(new workbox.strategies.StaleWhileRevalidate());

  workbox.routing.setCatchHandler(({event}) => {
    switch (event.request.destination) {
      case 'image':
        return caches.match(FALLBACK_IMAGE_URL);
      default:
        return Response.error();
    }
  });

}
