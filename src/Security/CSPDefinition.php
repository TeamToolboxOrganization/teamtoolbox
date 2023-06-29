<?php

namespace App\Security;

class CSPDefinition{


    public const defaultRules = "default-src https:; img-src *; script-src *; style-src *; font-src 'self' data:";

    public const PROD_RULES = "default-src 'none'; " . // Block all by default
    "script-src 'self' https://www.google-analytics.com https://ssl.google-analytics.com https://google-analytics.com https://www.googletagmanager.com; ".
    "connect-src 'self' https://www.google-analytics.com www.google-analytics.com https://stats.g.doubleclick.net https://www.googletagmanager.com; " .
    "img-src 'self' https://www.google-analytics.com www.google-analytics.com https://stats.g.doubleclick.net; " .
    "script-src-elem " .
    "'sha384-qNJvc5vTIEJWoiw5qyuSVqSWh5YdeZUC5wJ+LMqEwYLypmfB2e4/3w2+f5527OVe' " .// /build/runtime.js
    "'sha384-H95yDZfujuTEEMhE4Dis4R2ApcQs0L474d0DbzBDZNI/QEM9KK3Yr6yVV6AMLrA6' " .// /build/755.js
    "'sha384-wcMSCzpRwff8qle99NfsMZTIYKhVNFjzof+trrGamHe1TNcLEddu4D6+mxVXna8T' " .// /build/app.js
    "'sha384-TQBaa/jDfeCi3QFRHAjnH65DvYqPu/SovH/xqD12A1GMFihyHuVvau+s8z4dlxTn' " .// /build/320.js
    "'sha384-CCUFN6UIlZ+aPqwwCKTvLDSieBu0djxZgqfa8AwsdxWm0QMxGsDJlO9Uxhismrd9' " .// /build/186.js
    "'sha384-Dok3OoelGsmyRKg36QHKRQpRBafR+MfjltVhRG919GAno69TWWIh9QP3yxwBCVCM' " .// /build/474.js
    "'sha384-aJVTgNGll5sha9KdXZwMtwrNFz+IJM5r/3NfkLAv8Msmrm9q6gpGJcm7JuKAPqXr' " .// /build/localstorage.js
    "'sha384-9plsBRYyZ4mMUf89pCaUoD5CrgW6nuNhfikSanfn3WKInjWPBl/bZPsEKPJ6MuXl' " .// /build/base.js
    "'sha384-Q5IJjMWKvzOc5oqlpecUphg0Fg97P7mNxBqsA4U+0pkfMFOwvtTFnYkJeKfhJYU6' " .// /build/googleanalytics.js
    "'sha384-H68ox/YTaniBB1P2tuUjm0wZupfcomKy4Jzgp955hhM8X3ZfKibxTQTQl5Sq2nEu' " .// /build/note.js
    "'sha384-fsvB8nHAEt8L3GuDom5SdAXeKF1lIMqmyLZfDCPcmBPqdrNJA4+f6jvjG20nqdS5' " .// /build/readNote.js
    "'sha384-67EfAKWBxDJ3zXNgccUnHEqjfvSt+AOiuqoYmmozJCGwgLaK9Zmmk3T/Oh0qyftE' " .// /build/notification.js
    "'sha384-IvA+FDNhGufwncjjOwYfhH2r2Rj/KJc5g/+0/Ccd5tsVq4Gwzfx4wWYzLt2Soyir' " .// /build/403.js
    "'sha384-+nQtakZVStuX5OH1CZXI05OFtKfA0kDmcdG8O0zTwGYVNCqsJ+4WmDM32vfr1b1Q' " .// /build/chart.js
    "'sha384-zRZRh0+Dn0VwdGBkJCym5juZm/J5lC8R2a1WFe/cEuWF8jKe9+UdfPytJX2t1gE7' " .// /build/mep_action.js
    "'sha384-OSkw/ZceLZPR7KPYQYd2I4LhqmVCfdoLCv1OVFkmu4sLxFP/BfYbUHOdSEBjGBJP' " .// /build/calendar.js
    "; " .
    "style-src *; font-src 'self' data:; manifest-src 'self'; worker-src 'self';";

    public const DEV_RULES = "default-src 'none'; " . // Block all by default
    "img-src *; " .
    "script-src-elem " .
    "'sha384-cQTPYwqeQviWst3MLU6Paa4WzQwtzaq35tgBYr0UizP/sscDFTMdVN/8SaFIw7pW' " .// /build/runtime.js
    "'sha384-cMCXJJYeMM/bXfmfG4eBlh0LQfnuFIfvBfqC6AJwLWo3ObWGott1zxGIhL/HBCXj' " .// /build/vendors-node_modules_jquery_dist_jquery_js.js
    "'sha384-8GI65a/cJZT6OIXPnXejBniO3U0Hj/kHBnne20DMT3Z+L1qP95AhHTu7bEQmEv2I' " .// /build/vendors-node_modules_bootstrap-sass_assets_javascripts_bootstrap_alert_js-node_modules_bootst-84abc7.js
    "'sha384-Sw2NKQGJFgdsNlsF66et1QfSTRWXttqcp/a8l2EVpB63cnMsJeuiHtqk5rCMrbIl' " .// /build/app.js
    "'sha384-/SnvCTQ5heELo05rRbRNajTKmHR4wYI1muonCljWLI5uHwVeF+vKmeu7gDCNXxVR' " .// /build/vendors-node_modules_core-js_internals_redefine_js.js
    "'sha384-Z3lubSx6ovWhVjgm1+QynhuaAcYAUQCa695YAMHTUWY8aqOGlY+BS9JPQydOvlDV' " .// /build/vendors-node_modules_core-js_modules_es_array_for-each_js-node_modules_core-js_modules_web_do-31add8.js
    "'sha384-ZR6jJyTx1y4P8cf9QrokBr8c5DkeUHuboKu8HvDVNkjWtkMq20KI6ok0OQ5jXxiL' " .// /build/vendors-node_modules_core-js_modules_es_array_join_js-node_modules_core-js_modules_es_object_-b0c6cd.js
    "'sha384-Vwa4oEAqPjr82BNw+O0hVgT0oYJyOyAA2KlawBzmr2Wcb98uZ5/HbCOBBXcaJvM4' " .// /build/localstorage.js
    "'sha384-+FbfDudUmj1Uw1xlYCv5E0AunNBWilXMwBSvXjqEWL0cJBmoPu/MzBRCL7uj2UZL' " .// /build/base.js
    "'sha384-//2rrMMV2VIjaNnqM4ACeHXTBnUP0ji6gTrXtovTWIg4Nt+2YFSGfgss6EQ+RPmH' " .// /build/googleanalytics.js
    "'sha384-JFihuU6z5/RTVXMO/I+2WvNMqO1wwFZjla8EhRAvu04oGXJa+vTP+/2DO5KVwfQc' " .// /build/note.js
    "'sha384-hkjiDA0ZrB9QNPdFk/qwFw/bQk3SIvWXy4mSQFGqr6hsmvUgPbHDoJe55j9NN6Bd' " .// /build/readNote.js
    "'sha384-/0GO6l3J+es6phsgPcW6q3ztswVnOslxHYmwGVmzQC5rkL1IFpgC1ApPgoMETEzt' " .// /build/notification.js
    "'sha384-vcjWExPiqCkmu+HGTMQsChBKq7uhbZ8qiLJ5MWSWLTikoepyVzQESczJjxbNuHJc' " .// /build/vendors-node_modules_chart_js_auto_auto_esm_js.js
    "'sha384-2M/Oe2bzOyKyWmO0EcvVSfzW+dPVj0/8KuVMkmAHsNpjWOmVDGTzfnwrEQ4z4Wj9' " .// /build/chart.js
    "'sha384-5Ww9KzKUMZrcg4uAqRVpi5a7TIAl6HONAee4GR/3MEEWDKpqUAnAifdGfeHRB8tZ' " .// /build/vendors-node_modules_fullcalendar_core_main_js-node_modules_fullcalendar_interaction_main_js--e73da8.js
    "'sha384-K26hiewzpJotmsBo4TFz76/ig1M3HdA2GP0VFpkvMVDVz1fnCyvzA/qprfU2oCHI' " .// /build/calendar.js
    "'sha384-PH2s3301hURF3pquj7uY6NUvWSW0pQrsXggj/yno0H/Jrml9GsATG+GkJ1ZFVRSb' " .// /build/mep_action.js
    "'sha384-j4jH2p3p5zqstNltPi5MqSaGnv/JNLvIiPqBLOedBdIp3ue4FMhhsxvVp5VDBbTw' " .// /build/mep.js
    "'sha384-4xy5Kvtgh0knB5T8bD7AEEEed8lZvfIuW5ITRWkVW7gtmBnjOAFGJh6vMKVlZg2Y' " .// /build/newNote.js
    "'sha384-z3AogwdiyN19x77flZI7DfcLrS4YENsxv47hkKZ1tQcKDA9YNMlHdemAZb5gJe+F' " .// /build/vendors-node_modules_core-js_modules_es_date_to-string_js-node_modules_core-js_modules_es_fun-6d01ae.js
    "'sha384-GuGa05EUNE3XKrewlobBCl810vVniBjy6aT5FwHp1Yv9VjcB2Inxmg6EbGEzesPS' " .// /build/gantt_staffing.js
    "'sha384-GuGa05EUNE3XKrewlobBCl810vVniBjy6aT5FwHp1Yv9VjcB2Inxmg6EbGEzesPS' " .// /build/gantt_view.js
    "; " .
    "style-src *; font-src 'self' data:; connect-src 'self'; manifest-src 'self'; worker-src 'self';";

}