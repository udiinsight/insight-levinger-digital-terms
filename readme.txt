=== Insight - Levinger - Digital Terms ===
Contributors: insightmarketing
Requires at least: 6.0
Requires PHP: 8.0
Stable tag: 2.2.0
License: GPLv2 or later

Bricks dynamic data tags for the pre-exam digital terms page (/pre-exam-instructions/) and the
pre-surgery instructions page (/pre-surgery-instructions/).

== Description ==

Registers a "תקנון דיגיטלי" group of Bricks dynamic tags, filled from the page URL and from the
matching medical-center page:

* {ildt_patient}, {ildt_doctor}, {ildt_date}, {ildt_time} — from ?patient=, ?doctor_name=, ?date= (YYYY-MM-DD or DD/MM/YYYY), ?time= (HH:MM)
* {ildt_health_url}, {ildt_pay_url} — from ?health= and ?pay= (http/https only, URL-encoded)
* {ildt_center_name} (post title), {ildt_center_address}, {ildt_center_parking},
  {ildt_center_transportation}, {ildt_center_waze}, {ildt_center_fax} — from ?center= (medical-center slug or post ID)
* {ildt_op} — procedure name from ?op= (as sent by Salesforce); {ildt_op_prk} is "1" when it contains PRK or LASEK
* Pre-surgery branch fields ("דף הנחיות לפני ניתוח" group on the medical-center page): {ildt_center_glasses},
  {ildt_center_soft_lenses}, {ildt_center_hard_lenses}, {ildt_center_bank}, and ready links
  {ildt_center_finance_wa}, {ildt_center_finance_email}, {ildt_center_finance_phone}, {ildt_center_phone},
  {ildt_center_wa}, {ildt_center_email}

Any page whose Bricks content uses these tags is served noindex, no-referrer and uncached.

== Changelog ==

= 2.2.0 =
* feat: pre-surgery page support — ?op= procedure name, PRK/LASEK flag, fax, and a pre-surgery ACF field group on medical-center pages.

= 2.1.1 =
* fix: doctor param renamed to ?doctor_name= — ?doctor= is the doctor CPT query var and redirected to the doctors archive.

= 2.1.0 =
* feat: {ildt_doctor} from ?doctor= (renamed in 2.1.1).
* feat: {ildt_pay_url} from ?pay= replaces {ildt_area_url} (?area= removed; the personal-area link is now fixed in the page).

= 2.0.2 =
* Center name comes from the medical-center post title.
* Address, parking and transportation keep line breaks and bold, strip other HTML.

= 2.0.0 =
* Bricks dynamic tags replace the shortcode; the page is built in Bricks.

= 1.0.0 =
* First version (shortcode).
