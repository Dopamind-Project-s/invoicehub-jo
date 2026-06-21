# JoFotara UAT Verification — 2026-06-21

## Verdict

**UAT status: READY for controlled live UAT submission** once real establishment credentials are configured and a ready invoice is selected.

This verification did not redesign or rewrite the JoFotara integration. The working UBL preparation/building layer from the earlier mainline implementation remains intact; the current MVP invoice details action is wired into that same service path.

## Submit Button Visibility

The invoice details page renders the clear action **إرسال إلى نظام الفوترة الوطني** only when all runtime rules are satisfied:

- invoice status is `ready`;
- establishment has `JOFOTARA_SUBMIT`;
- establishment has Client ID, Secret Key, and source ID;
- establishment is active;
- authenticated user can `invoices.submit`;
- invoice is not already accepted/submitted by JoFotara.

If an invoice is ready but not submittable, the UI shows specific Arabic warnings for missing feature access or incomplete JoFotara credentials.

## Route → Controller → Service Flow

1. Invoice details view posts to `company.invoices.jofotara.submit`.
2. Route `POST /companies/{company}/invoices/{invoice}/jofotara-submit` calls `InvoiceEngineController::submitToJofotara` and is protected by `permission:invoices.submit`.
3. `InvoiceEngineController::submitToJofotara` calls `JoFotaraApiService::submit($invoice)`.
4. `JoFotaraApiService::submit` calls `JoFotaraPreparationService::prepare($invoice)`.
5. `JoFotaraPreparationService::prepare` calls `UBLInvoiceBuilder::build($invoice)`, canonicalizes and hashes XML, stores XML/payload artifacts, and returns a base64 JSON payload.
6. `JoFotaraApiService::submit` sends the payload to `services.jofotara.url` using `Client-Id` and `Secret-Key` headers.
7. `JoFotaraResponseParser::parse` extracts official response keys and `JoFotaraApiService` stores the safe response, UUID, QR, status, submission log, and timestamps.

## Real Service Verification

Runtime submission uses Laravel's real HTTP client through `Http::withHeaders(...)->post($endpoint, $payload)`. There are no runtime fake UUID, fake QR, or placeholder response values in the production service path. HTTP fakes are limited to tests.

A fallback UUID is only generated if JoFotara does not return an invoice UUID, so accepted UAT responses should be checked for `EINV_INV_UUID` presence before sign-off.

## Credentials Verification

Submission loads credentials in this order:

- establishment/company `jofotara_client_id`;
- establishment/company `jofotara_secret_key`;
- fallback environment config only if establishment credentials are empty.

The source ID and tax number are taken from the invoice supplier/company during XML checks and builder output. Credentials are encrypted at rest by the `Company` model accessors/mutators and hidden from array/JSON output.

## XML Verification

The active XML path is the existing JoFotara UBL path:

- `JoFotaraPreparationService::prepare` ensures identifiers, recalculates totals, resolves PIH, calls `UBLInvoiceBuilder::build`, canonicalizes/hashes XML, writes XML/canonical/payload artifacts, and validates checks.
- `UBLInvoiceBuilder` creates a UBL Invoice document, includes `cbc:ProfileID`, `cbc:UUID`, `cbc:InvoiceTypeCode`, seller/buyer parties, seller source ID, totals, taxes, and invoice lines.
- `git diff --exit-code a8d8d13..HEAD -- app/Services/Jofotara/JoFotaraPreparationService.php app/Services/Jofotara/UBLInvoiceBuilder.php app/Services/Jofotara/InvoiceTypeCodeService.php app/Services/Jofotara/InvoiceHashService.php app/Services/Jofotara/ICVService.php app/Services/Jofotara/TaxCalculationService.php app/Services/Jofotara/UBLValidationService.php` returned exit code 0, confirming these core builder/preparation classes match the earlier working implementation baseline available in repository history.

## Response Storage Verification

Current storage points:

- `invoice_submission_logs.submission_uuid`, `status`, `http_status`, `request_payload`, `response_body`, `error_message`, `attempt`, `submitted_at`;
- `invoices.submission_uuid`, `submission_response`, `qr_code`, `submitted_at`, `accepted_at`;
- `invoices.jofotara_status`, `jofotara_uuid`, `jofotara_qr`, `jofotara_response`, `jofotara_submitted_at`, `jofotara_error_message`.

The parser extracts `EINV_STATUS`, `EINV_QR`, `EINV_INV_UUID`, `EINV_RESULTS`, and `EINV_MESSAGE`.

## QR / UUID Rendering Verification

Invoice details show JoFotara status, UUID, date, message, safe response summary, and QR/barcode when present. Printable/PDF HTML shows QR/UUID after successful submission and only shows a placeholder before any QR/UUID exists.

## Mainline Comparison

The core preparation/XML/mapping/hash/tax/validation services are unchanged from the earlier working baseline commit `a8d8d13` in repository history. The MVP added company-scoped UI/routes and response persistence fields around the same core service path.

No missing JoFotara submission components were identified during this verification, so no mainline restoration patch was required.

## Import / Sync Verification

No real official historical retrieval endpoint is implemented in the current repository. The current supported Phase 1 path is JSON/CSV backfill through the JoFotara import page. It stores imported invoices as `source = jofotara_import` and prevents duplicates using JoFotara UUID or invoice number + issue date.

## UAT Readiness Matrix

| Area | Verdict | Notes |
| --- | --- | --- |
| Submit Button Visibility | READY | Button appears only for eligible ready invoices. |
| Route Wiring | READY | Company-scoped POST route calls the submit controller action. |
| Credential Loading | READY | Uses decrypted establishment credentials with env fallback. |
| XML Generation | READY | Existing UBL builder/preparation path preserved. |
| Submission | READY | Real runtime path uses Laravel HTTP client and configured endpoint. |
| Response Parsing | READY | Extracts official EINV keys. |
| UUID Storage | READY | Stores `EINV_INV_UUID` in invoice/submission fields. |
| QR Storage | READY | Stores `EINV_QR` in invoice QR fields. |
| QR Display | READY | Details page displays QR when present. |
| PDF QR Display | READY | Printable/PDF view displays QR/UUID when present. |
| Import Foundation | READY | JSON/CSV backfill exists with duplicate prevention. |
| Sync Foundation | NOT READY | No official historical pull endpoint exists in current code; do not invent one. |

## Remaining UAT Risks

- A real live UAT submission still requires valid JoFotara Client ID, Secret Key, source ID, tax number, and network access to the official endpoint.
- If JoFotara accepts a response without `EINV_INV_UUID`, the service stores a generated fallback UUID; this should be treated as a UAT warning and verified against real responses.
- Automatic historical sync remains pending until an official retrieval endpoint/access is provided.
