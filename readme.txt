=== Insight - Levinger - Digital Terms ===
Contributors: insightmarketing
Requires at least: 6.0
Requires PHP: 8.0
Stable tag: 2.0.2
License: GPLv2 or later

Bricks dynamic data tags for the pre-exam digital terms page (/pre-exam-instructions/).

== Description ==

Registers a "תקנון דיגיטלי" group of Bricks dynamic tags, filled from the page URL and from the
matching medical-center page:

* {ildt_patient}, {ildt_date}, {ildt_time} — from ?patient=, ?date= (YYYY-MM-DD or DD/MM/YYYY), ?time= (HH:MM)
* {ildt_health_url}, {ildt_area_url} — from ?health= and ?area= (http/https only, URL-encoded)
* {ildt_center_name} (post title), {ildt_center_address}, {ildt_center_parking},
  {ildt_center_transportation}, {ildt_center_waze} — from ?center= (medical-center slug or post ID)

Any page whose Bricks content uses these tags is served noindex, no-referrer and uncached.

== Changelog ==

= 2.0.2 =
* Center name comes from the medical-center post title.
* Address, parking and transportation keep line breaks and bold, strip other HTML.

= 2.0.0 =
* Bricks dynamic tags replace the shortcode; the page is built in Bricks.

= 1.0.0 =
* First version (shortcode).
