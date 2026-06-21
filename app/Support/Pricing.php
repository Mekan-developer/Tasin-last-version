<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Storefront pricing — единый источник истины по ценам для покупателя.
 *
 * Админка хранит «сырую» цену товара в USD или TMT. Витрина показывает
 * покупателю цену только в TMT:
 *   • цена в USD → price × tmt_rate × (1 + markup%)   (себестоимость + наценка)
 *   • цена в TMT → price как есть                      (это уже розничная TMT-цена)
 *
 * Никогда не считай цену на фронте — вызывай эти методы и отдавай готовую
 * строку («12 900 TMT»).
 */
class Pricing
{
    /** Курс TMT за 1 USD. */
    public static function tmtRate(): float
    {
        $rate = (float) Setting::get('tmt_rate', 19.50);

        return $rate > 0 ? $rate : 19.50;
    }

    /** Торговая наценка в процентах. */
    public static function markupPercent(): float
    {
        return max(0.0, (float) Setting::get('markup', 15));
    }

    /** Цена для покупателя в TMT, округлённая до целого маната. */
    public static function tmt(float|int|string|null $price, ?string $currency): int
    {
        $price = (float) $price;

        if ($price <= 0) {
            return 0;
        }

        if (strtoupper((string) $currency) === 'TMT') {
            return (int) round($price);
        }

        $tmt = $price * self::tmtRate() * (1 + self::markupPercent() / 100);

        return (int) round($tmt);
    }

    /** «12 900 TMT» — разряды через пробел, под шрифт Inter. */
    public static function format(int $tmt): string
    {
        return number_format($tmt, 0, '.', ' ').' TMT';
    }

    /** Готовая строка цены для покупателя. */
    public static function tmtFormatted(float|int|string|null $price, ?string $currency): string
    {
        return self::format(self::tmt($price, $currency));
    }

    /** Цена как есть — без конвертации, с исходной валютой («1 990 $» или «1 990 TMT»). */
    public static function rawFormatted(float|int|string|null $price, ?string $currency): string
    {
        $amount = number_format((int) round((float) $price), 0, '.', ' ');
        $cur    = strtoupper(trim((string) $currency)) ?: 'TMT';
        $symbol = $cur === 'USD' ? '$' : $cur;

        return "{$amount} {$symbol}";
    }
}
