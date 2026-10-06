# Phase 5 fulfillment setup

Run `php spark migrate` and `php spark db:seed PhaseFiveSeeder` after deploying this code. The seeder is idempotent. It creates manual, Steadfast, Pathao and RedX courier choices, and inactive CellFin/DBBL manual-payment methods.

## Courier workflow

1. Confirm an order, then open **Orders → Fulfillment**.
2. Create the parcel in the chosen courier's merchant panel. Save its tracking number/reference and, optionally, its official HTTPS tracking link in Taharat Agro.
3. Record the booking, then mark the order shipped. Continue updating shipment events as the parcel moves. A delivered shipment completes an already-shipped order.

The courier adapter interface separates provider booking and tracking from order status. All providers currently use the manual adapter: no external courier API request is made. Switching to API mode fails closed until a verified provider-specific adapter, account credentials, location mapping and webhook/polling contract are supplied. Do not enable a guessed API endpoint. Cancelling an order also marks a prepared/booked shipment cancelled in Taharat Agro; any merchant-panel booking must still be cancelled with the courier.

## Customer SMS

Order placed, confirmed, shipped, delivered, cancelled and courier-booked events create an idempotent outbox entry. SMS is **off by default**. To enable Alpha SMS (sms.bd), put `sms.apiKey` and optionally `sms.senderId` in private `.env`, then enable and edit templates at **Admin → Order SMS**. The key is not stored in the database or shown in admin.

Enabled messages are attempted after the related transaction commits. Failed sends remain in the outbox with bounded retries. Schedule `php spark sms:dispatch` once per minute with Windows Task Scheduler or cron, or retry individual messages in admin. An interrupted send becomes `needs_review` instead of being automatically resent, because the gateway may have accepted the request before the connection failed. Check the gateway log before manually retrying. Disabled/unconfigured events are recorded as `suppressed` and are not sent retroactively after enabling SMS.

## Payments

CellFin and DBBL Bank Transfer are manual verification methods, not online gateway integrations. At **Admin → Payment Methods**, enter the recipient account/number and clear customer instructions before enabling either. Checkout then requires a transaction/deposit reference and creates a pending-verification payment; an admin must verify it. Existing COD, bKash and Nagad settings are not overwritten.
