# نظام إدارة المعارض الرقمي (Digital Exhibition Management System) 🚀

هذا المشروع هو النظام الخلفي (Backend) لمشروع التخرج، يهدف لإدارة المعارض، العارضين، والطلبات بشكل مؤتمت.

## ✨ الميزات الأساسية
* **إدارة المنتجات والمخزن:** خصم تلقائي للكميات عند الموافقة على الطلبات.
* **نظام طلبات ذكي:** منع تكرار الطلبات (Spam protection) وحساب تلقائي للفواتير.
* **صلاحيات المستخدمين:** (Admin, Exhibitor, Visitor) باستخدام JWT.
* **API موحد:** جميع الردود تتبع تنسيق JSON احترافي وثابت.

## 🛠 المتطلبات التقنية
* PHP >= 8.1
* Laravel 10/11
* MySQL Database

## 🚀 خطوات التشغيل (للجنة المناقشة)
1. قم بتحميل المشروع: `git clone [https://github.com/shahdmaki/exhibition-system-backend ]`
2. تثبيت المكتبات: `composer install`
3. إعداد ملف البيئة: `cp .env.example .env` 
4. توليد المفتاح: `php artisan key:generate`
5. تهجير قاعدة البيانات: `php artisan migrate --seed`
6. تشغيل السيرفر: `php artisan serve`

## 📂 توثيق الروابط (API Documentation)

`./postman_collection.json` 

---
**إعداد الطالبة:** شهد أحمد المكي
**بإشراف:** [هشام حسن ]