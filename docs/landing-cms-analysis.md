# Landing Page CMS Analysis & Phase 1 Database Design

## Scope

This document analyzes the current landing, guest, and marketing theme files and proposes a Phase 1 CMS architecture for InvoSync. No feature implementation is included here.

Phase 1 principles:

- Reuse existing tables where they already model the required capability.
- Avoid duplicate CMS tables for values that fit a reusable setting model.
- Make Arabic content the primary content path, with English fields optional.
- Use the term `منشأة` instead of `شركة` in admin labels and landing copy.
- Keep Blog CMS and subscription/billing workflow out of Phase 1.

## Files inspected

- `resources/views/welcome.blade.php`
- `resources/views/layouts/header.blade.php`
- `resources/views/layouts/footer.blade.php`
- `routes/web.php`
- `database/migrations/2026_06_19_000003_create_feature_keys_and_subscriptions.php`
- `database/migrations/2026_06_19_000014_refine_feature_keys_and_plans.php`
- `app/Models/Plan.php`
- `app/Models/FeatureKey.php`
- `app/Http/Controllers/Admin/PlanController.php`

## 1. Theme section inventory

| Section | Location | Current content | Classification | CMS recommendation |
|---|---|---|---|---|
| Header / Navbar | `layouts/header.blade.php` | Logo, brand name, anchor links, login and get started buttons, theme toggle | Dynamic | Manage brand identity, nav links, CTA text/URL, and SEO title through settings. Keep theme toggle static. |
| Hero | `welcome.blade.php` | Badge, headline, subtitle, primary CTA, video CTA, dashboard mockup, trusted-by logos | Dynamic | Create hero slide/content management. Multiple slides are not currently implemented, but schema should support slide ordering. |
| Hero dashboard mockup | `welcome.blade.php` | Decorative dashboard cards, sidebar labels, chart, chat bubbles | Static for Phase 1 | Treat as visual illustration unless admin needs to edit demo metrics later. Replace copy with InvoSync Arabic copy during implementation. |
| Social proof / statistics | `welcome.blade.php` | Four metric cards and scrolling partner logo names | Dynamic | Use `landing_statistics` for metrics and `landing_partners` for logo/name strip. |
| Problem | `welcome.blade.php` | Section label, heading, subtitle, three problem cards | Dynamic | CMS content cards are recommended because current template is generic AI copy and must become invoice/Jordan/JoFotara specific. Could be a generic `landing_content_blocks` table or settings JSON in Phase 1. |
| Features | `welcome.blade.php` | Six feature cards with icon, title, description, tag | Dynamic | Reuse existing `feature_keys` for product capabilities where possible. Add marketing-only display fields only if needed. |
| How it works | `welcome.blade.php` | Three step cards | Dynamic | Manage as ordered content blocks or a small reusable section-item table. |
| Integrations | `welcome.blade.php` | Six integration cards | Dynamic | Add `landing_integrations` because integrations are marketing/site content and not equivalent to feature keys. |
| Pricing | `welcome.blade.php` | Three hardcoded pricing cards and monthly/yearly toggle | Dynamic | Reuse existing `plans` and `feature_key_plan`. Add missing display fields to `plans`. Do not implement subscriptions in Phase 1. |
| Testimonials | `welcome.blade.php` | Three testimonial cards with stars, text, avatar, name, position/company | Dynamic | Add `landing_testimonials`. |
| FAQ | `welcome.blade.php` | Four accordion items | Dynamic | Add `landing_faqs`. |
| Final CTA | `welcome.blade.php` | Badge, headline, subtitle, two CTA buttons | Dynamic | Store in reusable landing settings or hero/content blocks. |
| Footer | `layouts/footer.blade.php` | Brand, description, newsletter input, grouped links, copyright, social icons | Dynamic | Store contact, social links, copyright, footer groups, and SEO settings via reusable settings plus optional footer link table. |
| Login / signup offcanvas | `layouts/footer.blade.php` | Auth UI markup and JS targets | Static / existing auth | Not a marketing CMS section. Wire to real auth separately if needed. |
| Embedded dashboard demo | `layouts/footer.blade.php` | Large hidden/in-page dashboard demo with overview, agents, chat, analytics, automations, integrations, settings | Static / remove later | Not Phase 1 CMS. Consider removing or isolating from guest footer because it is demo UI, not landing content. |

Sections not found in the inspected template: About, Blog, Team, Download App, dedicated Contact Us form, dedicated Video section beyond the hero demo link, and screenshot gallery beyond the hero dashboard mockup.

## 2. Existing tables reused

| Capability | Existing structure | Reuse decision |
|---|---|---|
| Plans | `plans` | Reuse for pricing cards. |
| Feature keys | `feature_keys` and `feature_key_plan` | Reuse for plan included features and product feature descriptions where suitable. |
| Establishments | `companies` | Reuse only for SaaS customer establishments, not landing partners/testimonials. Landing copy should call them `منشآت`. |
| Users | `users` | Reuse for admin authentication and ownership/audit metadata. |
| Company settings | `company_settings` model exists | Do not reuse for global landing settings because it is company-scoped. Add a global settings table instead. |
| Routes/admin shell | Existing admin route group, admin controllers, sidebar | Reuse for the new `الموقع الإلكتروني` admin menu. |

## 3. Database gap analysis

| Section | Existing table | Needs new table | Reuse existing |
|---|---:|---:|---|
| Header / Navbar | No global settings table | Yes | Add `settings` or `site_settings`; optional `landing_nav_links`. |
| Hero | None | Yes | `landing_hero_slides`. |
| Hero dashboard mockup | None | No for Phase 1 | Keep static. |
| Social proof statistics | None | Yes | `landing_statistics`. |
| Partners / trusted-by logos | None | Yes | `landing_partners`. |
| Problem cards | None | Optional | Prefer `landing_content_sections` + `landing_content_items` if generic sections are desired. Otherwise settings JSON for Phase 1. |
| Features | `feature_keys` | Maybe | Reuse `feature_keys`; add marketing display metadata only if needed. |
| How it works | None | Optional | Prefer generic content section/items. |
| Integrations | None | Yes | `landing_integrations`. |
| Pricing | `plans`, `feature_key_plan`, `feature_keys` | No new pricing table | Add fields to `plans`. |
| Testimonials | None | Yes | `landing_testimonials`. |
| FAQ | None | Yes | `landing_faqs`. |
| Final CTA | None | No separate table | Use reusable global settings or content sections. |
| Contact information | No global settings table | Yes | Add reusable global settings table. |
| Social links | No global settings table | Yes | Store as grouped settings keys. |
| Footer settings | No global settings table | Yes | Store in settings; add footer links if admin needs link CRUD. |
| SEO settings | No global settings table | Yes | Store in settings for Phase 1. |
| Blog | None | No in Phase 1 | Explicitly defer. |
| Team | None | No in Phase 1 | Not present in template. |

## 4. Pricing section integration with existing plans

Current plan support:

- `plans.name` exists.
- `plans.slug` exists.
- `plans.description` exists after refinement migration.
- `plans.price`, `plans.monthly_price`, and `plans.yearly_price` exist.
- `plans.billing_cycle` exists but should remain legacy/compatibility for Phase 1.
- `plans.is_active` exists.
- `feature_key_plan` links plans to included feature keys.
- Admin CRUD already handles name, slug, description, monthly price, yearly price, active state, and included feature keys.

Missing or incomplete pricing fields:

| Requirement | Current support | Migration recommendation |
|---|---|---|
| Arabic Name | Partially through `plans.name` | Add `name_ar` and backfill from `name`; keep `name` as fallback/legacy. |
| English Name | Missing | Add nullable `name_en`. |
| Description | Single language only | Add `description_ar` and `description_en`, or treat current `description` as Arabic legacy and add `description_en`. Preferred: add both and backfill `description_ar` from `description`. |
| Monthly Price | Exists | Reuse `monthly_price`. |
| Yearly Price | Exists | Reuse `yearly_price`. |
| Included Feature Keys | Exists | Reuse `feature_key_plan`. |
| Sort Order | Missing | Add unsigned integer `sort_order` default 0 and index. |
| Active/Inactive | Exists | Reuse `is_active`. |
| Recommended Plan | Missing | Add boolean `is_recommended` default false and index. Ensure UI allows only one recommended plan, or accepts multiple if marketing wants multiple badges. |

Recommended migration for plans:

- Add `name_ar`, `name_en`, `description_ar`, `description_en`, `sort_order`, and `is_recommended` to `plans`.
- Backfill `name_ar = name`, `description_ar = description`.
- Update `Plan::$fillable` and admin validation.
- Landing query: active plans ordered by `sort_order`, then `monthly_price`, with eager-loaded active feature keys.

## 5. FAQ management design

Create `landing_faqs`:

| Field | Type | Notes |
|---|---|---|
| `id` | bigint | Primary key. |
| `question_ar` | string | Required. |
| `question_en` | string nullable | Optional English. |
| `answer_ar` | text | Required. |
| `answer_en` | text nullable | Optional English. |
| `category` | string nullable/indexed | Example: `general`, `pricing`, `jofotara`. |
| `sort_order` | unsigned integer default 0 | Landing ordering. |
| `is_active` | boolean default true/indexed | Landing reads active only. |
| `created_by`, `updated_by` | nullable foreign ids | Optional audit ownership. |
| timestamps | timestamps | Standard. |

Admin CRUD:

- List FAQs grouped/filterable by category.
- Create/edit/delete or soft-delete if deletion audit is required.
- Activate/deactivate buttons.
- Sort order field.
- Landing reads `where('is_active', true)->orderBy('sort_order')`.

## 6. Contact and footer management design

Preferred approach: add one reusable global `settings` table for singleton/global site values instead of separate contact, social, footer, and SEO tables.

Recommended `settings` table:

| Field | Type | Notes |
|---|---|---|
| `id` | bigint | Primary key. |
| `group` | string indexed | Examples: `site`, `contact`, `social`, `footer`, `seo`, `cta`. |
| `key` | string indexed | Example: `phone`, `whatsapp`, `meta_title_ar`. |
| `value` | text nullable | Scalar values or JSON strings. |
| `type` | string default `text` | `text`, `url`, `image`, `textarea`, `json`, `boolean`. |
| `locale` | string nullable/indexed | Null for shared; `ar` or `en` for localized settings. |
| `is_public` | boolean default true | Allows admin-only internal settings later. |
| timestamps | timestamps | Standard. |

Recommended settings keys:

- Contact: `phone`, `whatsapp`, `email`, `address_ar`, `address_en`, `google_maps_url`.
- Social: `facebook_url`, `instagram_url`, `linkedin_url`, `x_url`, `tiktok_url`, `youtube_url`.
- Footer: `footer_description_ar`, `footer_description_en`, `copyright_ar`, `copyright_en`, `newsletter_enabled`.
- SEO: `meta_title_ar`, `meta_title_en`, `meta_description_ar`, `meta_description_en`, `og_image`, `canonical_url`, `robots`.
- CTA: `primary_cta_text_ar`, `primary_cta_text_en`, `primary_cta_url`, `secondary_cta_text_ar`, `secondary_cta_text_en`, `secondary_cta_url`.

Footer links can be Phase 1 settings JSON if the link list is small. If admin needs true CRUD and ordering for multiple footer columns, add `landing_footer_links` with `group_ar`, `group_en`, `label_ar`, `label_en`, `url`, `sort_order`, and `is_active`.

## 7. Hero section CMS design

Create `landing_hero_slides` even though the current template only renders one hero. This keeps slider support simple if the theme later adds multiple slides.

| Field | Type | Notes |
|---|---|---|
| `id` | bigint | Primary key. |
| `badge_ar`, `badge_en` | string nullable | Small top badge. |
| `title_ar`, `title_en` | string/text | Main title; Arabic required. |
| `subtitle_ar`, `subtitle_en` | text nullable | Subtitle. |
| `primary_cta_text_ar`, `primary_cta_text_en` | string nullable | CTA label. |
| `primary_cta_url` | string nullable | CTA target. |
| `secondary_cta_text_ar`, `secondary_cta_text_en` | string nullable | Optional demo/sales CTA. |
| `secondary_cta_url` | string nullable | Optional video/demo/contact URL. |
| `background_image` | string nullable | Stored path. |
| `hero_illustration` | string nullable | Stored path for dashboard/screenshot/illustration. |
| `sort_order` | unsigned integer default 0 | Slider order. |
| `is_active` | boolean default true/indexed | Landing reads active only. |
| timestamps | timestamps | Standard. |

## 8. Integrations design

Create `landing_integrations`:

| Field | Type | Notes |
|---|---|---|
| `id` | bigint | Primary key. |
| `name_ar`, `name_en` | string | Arabic required, English optional. |
| `description_ar`, `description_en` | text nullable | Localized descriptions. |
| `icon` | string nullable | Font Awesome class or uploaded icon path. |
| `status` | string default `available` | Suggested values: `available`, `coming_soon`, `connected`, `planned`. |
| `sort_order` | unsigned integer default 0 | Display order. |
| `is_active` | boolean default true/indexed | Show/hide. |
| timestamps | timestamps | Standard. |

Initial InvoSync integration seed suggestions: JoFotara, ERP, POS, Excel, API, Future Integrations.

## 9. Testimonials design

Create `landing_testimonials`:

| Field | Type | Notes |
|---|---|---|
| `id` | bigint | Primary key. |
| `name` | string | Person name. |
| `establishment` | string nullable | Use admin label `المنشأة`. |
| `position` | string nullable | Job title. |
| `testimonial_ar` | text | Required Arabic. |
| `testimonial_en` | text nullable | Optional English. |
| `avatar` | string nullable | Uploaded avatar path. |
| `rating` | unsigned tiny integer default 5 | Current UI shows five stars; keep configurable. |
| `sort_order` | unsigned integer default 0 | Display order. |
| `is_active` | boolean default true/indexed | Show/hide. |
| timestamps | timestamps | Standard. |

## 10. Statistics and partners design

Create `landing_statistics`:

- `label_ar`, `label_en`
- `value`
- `suffix` nullable
- `icon` nullable
- `sort_order`
- `is_active`

Create `landing_partners`:

- `name_ar`, `name_en`
- `logo_path` nullable
- `url` nullable
- `sort_order`
- `is_active`

Do not reuse `companies` for partners because SaaS customer establishments and marketing partner logos have different ownership, privacy, and lifecycle requirements.

## 11. Landing CMS admin menu

Add a new Admin menu group named `الموقع الإلكتروني`.

Phase 1 menu entries:

1. Hero
2. FAQs
3. Testimonials
4. Integrations
5. Statistics
6. Contact Information
7. Footer
8. SEO
9. Partners

Implementation notes:

- These routes belong under the existing `auth` + `super.admin` admin route group.
- Use Arabic labels first.
- Content pages should support optional English fields but not require English values.
- Do not add Blog CMS in Phase 1.

## 12. Landing CMS architecture

Recommended architecture:

- Models:
  - `Setting`
  - `LandingHeroSlide`
  - `LandingFaq`
  - `LandingTestimonial`
  - `LandingIntegration`
  - `LandingStatistic`
  - `LandingPartner`
- Optional generic models if avoiding many small one-off tables:
  - `LandingContentSection`
  - `LandingContentItem`
- Controllers under `App\Http\Controllers\Admin\LandingCms`.
- Views under `resources/views/admin/landing-cms`.
- Public landing composition service, for example `LandingPageDataService`, to load active content and cache it.
- Cache key pattern: `landing:home:{locale}` with cache busting on admin save.
- Media uploads under `storage/app/public/landing/...`.
- Public route `/` should pass CMS data to `welcome` instead of rendering hardcoded content.

## 13. Phase 1 implementation order

1. Add settings infrastructure and contact/footer/SEO forms.
2. Extend `plans` for bilingual marketing fields, sort order, and recommended badge.
3. Create FAQ CRUD and wire the landing FAQ accordion to active FAQs.
4. Create hero slide CRUD and wire the hero section.
5. Create integrations CRUD and seed JoFotara, ERP, POS, Excel, API, and Future Integrations.
6. Create testimonials CRUD and wire testimonial cards.
7. Create statistics and partners CRUD and wire social proof sections.
8. Convert pricing cards to use active plans and included feature keys.
9. Add Admin sidebar group `الموقع الإلكتروني` with all Phase 1 pages.
10. Replace English/generic AI copy with Arabic-first InvoSync/JoFotara copy and optional English fallback.
11. Add feature tests for admin access, active-only landing reads, and pricing plan rendering.
12. Defer Blog, Team, Download, full Contact form, and subscriptions/billing workflows to later phases.
