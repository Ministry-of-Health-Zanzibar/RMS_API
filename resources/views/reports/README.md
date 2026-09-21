# Top diagnoses report

Open **Top Diagnoses** in the application sidebar (`/pages/patient/top-diagnoses`).
Users require the existing **View Report** permission. The initial period is
March 1, 2026 through the current day; select dates and click **Generate report**.

The authenticated endpoint is `GET /api/reports/top-diagnoses` with required
`start_date` and `end_date` parameters in `YYYY-MM-DD` format. Omit `format` for
JSON, or use `csv`, `pdf`, or `docx` to download a report.

Names are grouped exactly as stored, across diagnosis IDs and codes. Each patient
counts once per name; each history counts once even when the doctor and medical
board both recorded the diagnosis. The top ten are ordered by unique patient
count descending, then name ascending. Soft-deleted records are excluded.

Dates filter history creation and referral creation, using the application's
timezone. The end date includes the entire day, capped at the current time.
Hospitals are matched through `diagnosis_referral` by patient and diagnosis name;
the schema does not directly connect referrals to individual patient histories.
Patients without a matching referral still appear.

Exports have one row per patient per diagnosis, with hospital names in one cell.
Diagnosis-level totals repeat on patient rows and must not be summed. CSV uses
UTF-8 with a BOM for Excel and neutralizes spreadsheet formula prefixes.
PDF uses the existing DomPDF dependency; genuine Word `.docx` files use PHP's
ZipArchive extension. No database migrations or additional packages are needed.

Run the endpoint/export tests with:

```sh
php artisan test --filter=TopDiagnosesTest
```

These tests mock report retrieval to test authorization, validation, date
boundaries, and file formats without accessing the application database.
