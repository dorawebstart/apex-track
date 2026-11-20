<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\TrackSession;
use App\Models\User;
use Illuminate\Database\Seeder;

class TrackSessionSeeder extends Seeder
{
    /**
     * 19 трек-сесій на реальних авто. Фото — database/seeders/images (Wikimedia Commons, див. credits.json).
     * Дати відносні від «зараз», тож після повторного розгортання сесії знову актуальні.
     */
    public function run(): void
    {
        $adminId = User::where('role', 'admin')->value('id');
        $categories = Category::pluck('id', 'slug');

        foreach ($this->sessions() as $s) {
            [$slug, $category, $title, $car, $track, $hp, $speed, $accel, $days, $hours, $capacity, $status, $description] = $s;
            $startsAt = now()->addDays($days)->setTime(9 + ($days % 4) * 2, 0);
            $source = database_path("seeders/images/{$slug}.jpg");

            TrackSession::create([
                'category_id' => $categories[$category],
                'created_by' => $adminId,
                'title' => $title,
                'description' => $description,
                'car_model' => $car,
                'brand' => TrackSession::brandFromModel($car),
                'track_name' => $track,
                'horsepower' => $hp,
                'top_speed_kmh' => $speed,
                'zero_to_100' => $accel,
                'starts_at' => $startsAt,
                'ends_at' => $startsAt->copy()->addHours($hours),
                'capacity' => $capacity,
                'image_path' => is_file($source) && filesize($source) > 0 ? TrackSession::storeImage($source, 'jpg') : null,
                'status' => $status,
            ]);
        }
    }

    /**
     * [фото, клас, назва, авто, трек, к.с., макс. км/год, 0–100 с, через днів, тривалість год, місць, статус, опис]
     *
     * @return list<array{0: string, 1: string, 2: string, 3: string, 4: string, 5: int, 6: int, 7: float, 8: int, 9: int, 10: int, 11: string, 12: string}>
     */
    private function sessions(): array
    {
        return [
            ['mclaren-artura', 'supercars', 'Гібридний ранок на Slovakia Ring', 'McLaren Artura', 'Slovakia Ring', 680, 330, 3.0, 6, 3, 8,
                'published', 'Перше знайомство з гібридним суперкаром: електромотор дає миттєву тягу на виході з повільних шпильок, а V6 підхоплює на прямих. Брифінг з інструктором, три серії по 15 хвилин і телеметрія кожного кола.'],
            ['ferrari-296-gtb', 'supercars', 'Ferrari 296 GTB: точність на Hungaroring', 'Ferrari 296 GTB', 'Hungaroring', 830, 330, 2.9, 9, 4, 10,
                'published', 'Технічна траса з великою кількістю поворотів — ідеальне місце, щоб відчути баланс середньомоторної Ferrari. Інструктор у пасажирському кріслі допоможе знайти правильні точки гальмування.'],
            ['ferrari-296-gtb-grey', 'supercars', 'Вечірня серія 296 GTB Assetto', 'Ferrari 296 GTB Assetto Fiorano', 'Tor Poznań', 830, 330, 2.9, 15, 3, 6,
                'published', 'Полегшена версія Assetto Fiorano з налаштуваннями підвіски для треку. Невелика група, більше часу за кермом, розбір відео з бортової камери після заїзду.'],
            ['lamborghini-huracan-sto', 'supercars', 'Huracán STO: атмосферний V10', 'Lamborghini Huracán STO', 'Automotodrom Brno', 640, 310, 3.0, 4, 3, 2,
                'published', 'Останній атмосферний V10 у класі: задній привід, аеродинаміка з гоночного Super Trofeo і звук, який чути на всьому автодромі. Лише два пілоти — максимум часу на трасі.'],
            ['lamborghini-huracan-sto-green', 'supercars', 'Ранкова зміна Verde Mantis', 'Lamborghini Huracán STO', 'Red Bull Ring', 640, 310, 3.0, 20, 4, 8,
                'published', 'Швидкісна альпійська траса з перепадами висот. Програма для пілотів з досвідом: вільні серії після двох оцінних кіл з інструктором.'],
            ['ferrari-roma', 'grand-tourer', 'Ferrari Roma: гран-тур до треку', 'Ferrari Roma', 'Slovakia Ring', 620, 320, 3.4, 7, 5, 12,
                'published', 'Комфортний гран-турізмо, який на треку перетворюється на справжню Ferrari. Програма для новачків: теорія траєкторій, відпрацювання гальмування, дві тренувальні серії.'],
            ['aston-martin-dbs', 'grand-tourer', 'DBS Superleggera: V12 без поспіху', 'Aston Martin DBS Superleggera', 'Hungaroring', 725, 340, 3.4, 11, 4, 8,
                'published', 'Британський V12 з крутним моментом 900 Нм. Навчимося керувати масою великого GT і проходити швидкі повороти плавно, без зриву в занос.'],
            ['aston-martin-dbs-grey', 'grand-tourer', 'Клубний день Aston Martin', 'Aston Martin DBS Superleggera', 'Tor Poznań', 725, 340, 3.4, 25, 6, 10,
                'published', 'Довгий клубний день з обідом у паддоку. Кожен пілот отримує чотири серії та сертифікат з найкращим часом кола.'],
            ['mercedes-amg-gt-black-series', 'grand-tourer', 'AMG GT Black Series: Нюрбургрінг-ДНК', 'Mercedes-AMG GT Black Series', 'Automotodrom Brno', 730, 325, 3.2, 13, 3, 6,
                'draft', 'Найжорсткіша AMG з плоским колінвалом V8 та регульованим антикрилом. Сесія готується — очікуємо підтвердження дати від автодрому.'],
            ['ferrari-f40', 'hypercars', 'Легенда F40: аналоговий драйв', 'Ferrari F40', 'Red Bull Ring', 478, 324, 4.1, 10, 3, 4,
                'published', 'Без ABS, без підсилювача керма, без електроніки — лише турбо V8 і пілот. Обмежена кількість місць, обов\'язковий попередній заїзд на GT-авто.'],
            ['ferrari-f40-classic', 'hypercars', 'F40 Classic Experience', 'Ferrari F40', 'Hungaroring', 478, 324, 4.1, 30, 3, 4,
                'published', 'Колекційна F40 1990 року в ідеальному стані. Два кола з інструктором, три самостійні та фотосесія на піт-лейні.'],
            ['porsche-918-spyder', 'hypercars', 'Porsche 918 Spyder: гібрид-гіперкар', 'Porsche 918 Spyder', 'Slovakia Ring', 887, 345, 2.6, 17, 3, 6,
                'published', 'Повний привід, 887 к.с. та електричний режим E-Power для тихого виїзду з боксів. Сесія для пілотів, які вже мають досвід на суперкарах.'],
            ['mclaren-p1', 'hypercars', 'McLaren P1: Святий Грааль', 'McLaren P1', 'Automotodrom Brno', 916, 350, 2.8, 22, 3, 6,
                'published', 'Один із «святої трійці» гіперкарів. Режим Race опускає кліренс на 50 мм і висуває заднє крило — відчуйте притискну силу на швидких поворотах Брно.'],
            ['mclaren-p1-mso', 'hypercars', 'P1 MSO: архівний заїзд', 'McLaren P1 MSO', 'Tor Poznań', 916, 350, 2.8, -12, 3, 6,
                'archived', 'Минула сесія на ексклюзивній P1 від McLaren Special Operations. Збережена в архіві з історією бронювань.'],
            ['porsche-911-gt3-rs', 'track-only', '911 GT3 RS: школа точності', 'Porsche 911 GT3 RS (992)', 'Red Bull Ring', 525, 296, 3.2, 5, 4, 10,
                'published', 'Атмосферний оппозитник до 9000 об/хв і DRS на задньому крилі. Найкраще авто, щоб прокачати техніку: інструктор, телеметрія і порівняння з еталонним колом.'],
            ['mclaren-senna-gtr', 'track-only', 'Senna GTR: тільки трек', 'McLaren Senna GTR', 'Hungaroring', 825, 320, 2.8, 14, 3, 4,
                'published', 'Трековий McLaren з притискною силою понад 1000 кг. Гоночний комбінезон, шолом і HANS входять у вартість. Тільки для пілотів з ліцензією або досвідом.'],
            ['ferrari-f2004', 'track-only', 'Болід F1 Ferrari F2004', 'Ferrari F2004 (Formula 1)', 'Autodromo di Monza', 865, 360, 2.4, 19, 5, 3,
                'published', 'Болід, на якому Міхаель Шумахер виграв чемпіонат 2004 року. Теорія, симулятор, прогрівання шин і п\'ять кіл на справжньому болиді Формули 1 у супроводі механіків.'],
            ['ferrari-f2004-pit', 'track-only', 'F1 Pit Day: F2004 + піт-стоп', 'Ferrari F2004 (Formula 1)', 'Autodromo di Monza', 865, 360, 2.4, 33, 6, 6,
                'draft', 'Розширена програма з тренуванням піт-стопу разом з механіками. Дата уточнюється.'],
            ['red-bull-rb7', 'track-only', 'Red Bull RB7: шоу-кар F1', 'Red Bull RB7 (Formula 1)', 'Red Bull Ring', 750, 340, 2.4, 27, 4, 4,
                'published', 'Чемпіонський болід 2011 року з V8 2.4 л. Програма включає симулятор, медичний огляд і три кола за безпечним авто з поступовим збільшенням темпу.'],
        ];
    }
}
