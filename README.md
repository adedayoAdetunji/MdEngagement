# MdEngagement

A LifeTechOCMS module package providing contact enquiry handling and newsletter subscription management.

The module strictly conforms to the [LifeTechOCMS Module Development Conventions](https://github.com/lifetechOCMS/docs/blob/master/backend-development/module-dev/README.md) and MVC architecture.

---

## Architecture and Structure

```text
MdEngagement/
├── Controllers/
│   ├── TbEngagementEnquiryController.php       # Handles enquiry endpoints
│   ├── TbEngagementSubscriberController.php    # Handles newsletter endpoints
│   └── EngagementController.php                # Base helper controller
├── Models/
│   ├── TbEngagementEnquiry.php                 # Model extending \LtModel for tb_engagement_enquiry
│   └── TbEngagementSubscriber.php              # Model extending \LtModel for tb_engagement_subscriber
├── Services/
│   ├── MdEngagementRoutes.php                  # Module route definitions
│   ├── TbEngagementEnquiryService.php          # Business logic for contact enquiries
│   ├── TbEngagementSubscriberService.php       # Business logic for newsletter subscribers
│   └── EngagementService.php                   # Base helper service
├── Views/
│   ├── Contact.html                            # Public contact form view
│   ├── Manage-enquiries.html                   # Admin enquiry management view
│   ├── Manage-subscribers.html                 # Admin subscriber management view
│   ├── Newsletter-signup.html                  # Newsletter subscription view
│   └── Newsletter-unsubscribe.html             # Unsubscribe landing view
├── Storage/
│   └── schema.sql                              # Database table definitions (tb_ prefix)
├── package.json                                # Module metadata and dependencies
└── README.md                                   # Module documentation
```

---

## Conventions Followed

1. **MVC & Core Services**:
   - Controllers handle HTTP routing, request validation via `LtRequest` and `LtValidator`, and dispatch to Services.
   - Services contain reusable business logic and communicate with Models and the database driver (`\DbConnect::dbDriver()`).
   - Models represent database tables and inherit from `\LtModel`.
   - Protected endpoints verify user authentication using `LtAuth::check()` and `LtSession`.
2. **Naming Conventions**:
   - Module name: `MdEngagement` (PascalCase with `Md` prefix).
   - Namespaces: `Lt\Modules\MdEngagement\Controllers`, `Lt\Modules\MdEngagement\Models`, `Lt\Modules\MdEngagement\Services`.
   - Tables: `tb_engagement_enquiry`, `tb_engagement_subscriber` (`tb_` prefix, snake_case).
   - Models: `TbEngagementEnquiry`, `TbEngagementSubscriber` (PascalCase matching table names).
   - Controllers: `TbEngagementEnquiryController`, `TbEngagementSubscriberController`.
   - Services: `TbEngagementEnquiryService`, `TbEngagementSubscriberService`.
   - Route file: `Services/MdEngagementRoutes.php`.
   - Protected/private properties use an underscore prefix (e.g. `$_enquiryModel`, `$_request`, `$_sqlConnect`).
3. **Response Codes Policy**:
   - Uses the compulsory **Customized range `4000–5999`** with exactly 4 digits:
     - `4000`: Success (Category `200`)
     - `4001`: Input validation or bad request (Category `100`)
     - `4002`: Internal error or exception (Category `100`)
     - `4003`: Unauthorized / Staff access required (Category `100`)
     - `4004`: Record not found or invalid token (Category `100`)
4. **Code Style**:
   - 4-space indentation.
   - Opening braces `{` on the same line.
   - Lines kept within 120 characters.

---

## Installation and Setup

1. **Database Setup**:
   Import `Storage/schema.sql` into your MySQL/MariaDB database:
   ```bash
   mysql -u user -p lifetech_db < packages/modules/MdEngagement/Storage/schema.sql
   ```
2. **Staff Access Configuration (Optional)**:

   If set, only authenticated sessions matching these user IDs can access staff endpoints. If unset, all authenticated sessions passing `LtAuth::check()` can access staff endpoints.
3. **Sync Module Content**:
   In the LifeTechOCMS dashboard, go to **Modules** → **MdEngagement** → **Module Content** and click **Sync File** to register files.

---

## API Endpoints

All responses follow the standard `LtResponse` envelope format:
```json
{
  "responseResult": "Message received.",
  "responseCode": "4000",
  "responseCategory": "200",
  "responseData": { ... }
}
```

| Method | Route | Controller Action | Description |
|---|---|---|---|
| `POST` | `/engagement/enquiries` | `TbEngagementEnquiryController@store` | Submit a new contact enquiry |
| `POST` | `/engagement/subscribers` | `TbEngagementSubscriberController@store` | Subscribe an email address |
| `POST` | `/engagement/unsubscribe` | `TbEngagementSubscriberController@unsubscribe` | Unsubscribe with a token |
| `GET` | `/engagement/admin/enquiries` | `TbEngagementEnquiryController@index` | List enquiries (staff only, supports `page` and `status`) |
| `PATCH` | `/engagement/admin/enquiries/status` | `TbEngagementEnquiryController@update` | Update enquiry status (`new`, `read`, `resolved`) |
| `GET` | `/engagement/admin/subscribers` | `TbEngagementSubscriberController@index` | List subscribers (staff only, supports `page` and `status`) |
