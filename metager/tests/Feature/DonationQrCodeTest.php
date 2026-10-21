<?php

namespace Tests\Feature;

use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use SepaQr\SepaQrData;
use Tests\TestCase;

/**
 * Der Überweisungs-QR-Code auf /spende und sein Download unter /spende/qr.
 *
 * Eine Banking-App liest daraus Empfänger, IBAN und Verwendungszweck. Ein
 * kaputter Code fällt hier niemandem auf, sondern dem, der spenden wollte.
 *
 * Das erwartete Bild wird mit `QrCode` und `PngWriter` direkt gebaut, nicht
 * über den `Builder`: dessen API hat sich mit endroid/qr-code 6 vollständig
 * geändert, diese beiden nicht. So prüft derselbe Test den Code vor und nach
 * dem Umbau.
 */
class DonationQrCodeTest extends TestCase
{
    private static function expectedPng(?int $amount): \Endroid\QrCode\Writer\Result\ResultInterface
    {
        $payment = (new SepaQrData())
            ->setName("SUMA-EV")
            ->setIban("DE64430609674075033201")
            ->setBic("GENODEM1GLS")
            ->setCurrency("EUR")
            ->setRemittanceText(__('spende.execute-payment.banktransfer.qr-remittance', ["date" => now()->format("d.m.Y")]));
        if ($amount !== null) {
            $payment->setAmount($amount);
        }

        return (new PngWriter())->write(new QrCode(
            data: (string) $payment,
            errorCorrectionLevel: ErrorCorrectionLevel::High,
        ));
    }

    public function testTheDonationPageShowsTheTransferQrCode(): void
    {
        $this->freezeTime();

        $response = $this->get("/de-DE/spende")->assertOk();

        $response->assertSee('<img src="' . self::expectedPng(null)->getDataUri() . '"', false);
    }

    public function testTheTransferQrCodeCanBeDownloaded(): void
    {
        $this->freezeTime();

        $response = $this->get("/de-DE/spende/qr")->assertOk();

        $expected = self::expectedPng(10);
        $response->assertHeader("Content-Type", $expected->getMimeType());
        $response->assertHeader("Content-Disposition", "attachment; filename=suma_donation.png");
        $this->assertSame($expected->getString(), $response->getContent());
    }
}
