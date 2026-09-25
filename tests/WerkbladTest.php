<?php

declare(strict_types=1);

namespace HansDeBoeck\HansUi\Tests;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Blade;
use PHPUnit\Framework\Attributes\Test;

/**
 * Het werkblad: wat <x-panes>, <x-list-row>, <x-message>, <x-composer> en de
 * rest beloven, voor zover de server het kan waarmaken.
 *
 * Wat in de browser gebeurt (de sneltoetsen, het concept, Ctrl + Enter) staat
 * in tests/browser/werkblad.html. Hier staat dat de haken waarop dat gedrag
 * leunt, er ook echt staan: een rij zonder data-bulk-row kiest niets bij
 * Ctrl + klik, een antwoordvak zonder data-draft onthoudt niets, en geen van
 * beide meldt dat.
 */
final class WerkbladTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // De beweringen gaan over de componenten en niet over de taal waarin
        // de testapplicatie toevallig staat; het Engels staat apart hieronder.
        app()->setLocale('nl');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    #[Test]
    public function op_een_telefoon_toont_het_werkblad_de_lijst_of_wat_open_staat(): void
    {
        $open = Blade::render('<x-panes :detail="true"><x-slot:list>lijst</x-slot:list><x-slot:main>gesprek</x-slot:main></x-panes>');
        $dicht = Blade::render('<x-panes><x-slot:list>lijst</x-slot:list><x-slot:main>leeg</x-slot:main></x-panes>');

        $this->assertStringContainsString('panes panes-detail', $open);
        $this->assertStringNotContainsString('panes-detail', $dicht);

        // De zijkolom staat er alleen als er iets in staat: anders krijgt
        // het raster op een breed scherm een lege derde kolom.
        $this->assertStringNotContainsString('panes-aside', $dicht);
        $this->assertStringContainsString('<aside class="panes-aside pane"', Blade::render(
            '<x-panes><x-slot:list>l</x-slot:list><x-slot:main>m</x-slot:main><x-slot:aside class="pane">d</x-slot:aside></x-panes>',
        ));
    }

    #[Test]
    public function een_melding_in_het_werkblad_staat_buiten_de_kop(): void
    {
        // De kop verdwijnt op een telefoon zodra er iets open staat. Een
        // melding dat je antwoord vertrok, hoort daar niet mee te verdwijnen.
        $html = Blade::render('<x-panes :detail="true"><x-slot:head>Inbox</x-slot:head> <p>Verstuurd</p> <x-slot:list>l</x-slot:list><x-slot:main>m</x-slot:main></x-panes>');

        $this->assertMatchesRegularExpression('/<div class="panes-head">Inbox<\/div>\s*<p>Verstuurd<\/p>/', $html);
    }

    #[Test]
    public function een_rij_is_een_link_met_een_vinkje_erboven(): void
    {
        $html = Blade::render('<x-list-row href="/gesprek/7" :active="true" check="7" check-label="Kiezen: Nina" shortcut="j" :count="4"><x-slot:title>Nina</x-slot:title> Waar blijft het?</x-list-row>');

        // Ctrl + klik kiest, en het vinkje hoort bij data-bulk.
        $this->assertStringContainsString('data-bulk-row', $html);
        $this->assertMatchesRegularExpression('/<input type="checkbox" value="7" data-bulk-item aria-label="Kiezen: Nina">/', $html);

        // Wat open staat, zegt dat tegen een schermlezer en staat bij het laden in beeld.
        $this->assertMatchesRegularExpression('/<a href="\/gesprek\/7" class="list-row-link"\s+aria-current="true"\s+data-shortcut="j"\s*>Nina<\/a>/', $html);
        $this->assertStringContainsString('data-scroll-here="center"', $html);

        $this->assertStringContainsString('<span class="list-row-preview">Waar blijft het?</span>', $html);
        $this->assertStringContainsString('>4</span>', $html);
    }

    #[Test]
    public function een_rij_zonder_vinkje_of_onderwerp_heeft_er_ook_geen_plaats_voor(): void
    {
        $html = Blade::render('<x-list-row href="/gesprek/8" title="Jan" :count="1"><x-slot:subject></x-slot:subject></x-list-row>');

        $this->assertStringNotContainsString('data-bulk', $html);
        $this->assertStringNotContainsString('list-row-avatar', $html);
        $this->assertStringNotContainsString('list-row-subject', $html);
        $this->assertStringNotContainsString('list-row-count', $html, 'Een enkel bericht hoeft niet geteld.');
        $this->assertStringNotContainsString('data-scroll-here', $html);
    }

    #[Test]
    public function een_bericht_toont_wat_het_is(): void
    {
        $in = Blade::render('<x-message name="Nina" body="Hallo"/>');
        $uit = Blade::render('<x-message type="out" name="Hans" body="Dag"/>');
        $auto = Blade::render('<x-message type="auto" body="Ontvangen"/>');
        $mislukt = Blade::render('<x-message type="failed" name="Hans" body="Toch" error="Geen verbinding"><x-slot:retry><button>Opnieuw</button></x-slot:retry></x-message>');
        $notitie = Blade::render('<x-message type="note" name="Hans" body="Bellen"/>');

        $this->assertStringContainsString('class="message message-in"', $in);
        $this->assertStringContainsString('class="message message-out"', $uit);
        $this->assertStringContainsString('class="message message-out message-auto"', $auto);
        $this->assertStringContainsString('class="message message-out message-failed"', $mislukt);
        $this->assertStringContainsString('class="message-note"', $notitie);

        // Wie "we hebben je bericht" kreeg, heeft nog geen antwoord: dat staat erbij.
        $this->assertStringContainsString('Automatisch antwoord', $auto);

        // Wat niet vertrok, zegt waarom, met de knop om het opnieuw te proberen.
        $this->assertStringContainsString('Niet verstuurd: Geen verbinding', $mislukt);
        $this->assertStringContainsString('<button>Opnieuw</button>', $mislukt);

        // De tekst zoals ze geschreven werd, en ontsnapt.
        $this->assertStringContainsString('<p class="message-text">&lt;b&gt;vet&lt;/b&gt;</p>', Blade::render('<x-message body="<b>vet</b>"/>'));
    }

    #[Test]
    public function een_mail_is_een_kaart_met_een_kop(): void
    {
        $html = Blade::render('<x-message layout="card" name="Nina" address="nina@voorbeeld.be" to="hallo@winkel.be" body="Een mail"/>');

        $this->assertStringContainsString('<article class="message-card message-in"', $html);
        $this->assertStringContainsString('&lt;nina@voorbeeld.be&gt;', $html);
        $this->assertStringContainsString('aan hallo@winkel.be', $html);
    }

    #[Test]
    public function kort_hoe_lang_geleden(): void
    {
        Carbon::setTestNow('2026-03-10 12:00:00');

        $label = fn (string $tijd): string => trim(strip_tags(Blade::render('<x-ago :time="$t"/>', ['t' => $tijd])));

        $this->assertSame('nu', $label('2026-03-10 11:59:40'));
        $this->assertSame('12 min', $label('2026-03-10 11:48:00'));
        $this->assertSame('3 u', $label('2026-03-10 09:00:00'));
        $this->assertSame('2 d', $label('2026-03-08 10:00:00'));

        // Na een week de dag, en niet "3 w".
        $this->assertStringContainsString('18', $label('2026-02-18 10:00:00'));
        $this->assertStringContainsString('2025', $label('2025-11-02 10:00:00'));

        // Een klok die voorloopt is "nu", geen negatief getal.
        $this->assertSame('nu', $label('2026-03-10 12:05:00'));

        $this->assertStringContainsString('datetime="2026-03-10T09:00:00+00:00"', Blade::render('<x-ago time="2026-03-10 09:00:00"/>'));
        $this->assertSame('', trim(Blade::render('<x-ago/>')));
    }

    #[Test]
    public function een_dag_in_een_gesprek_in_de_tijdzone_van_de_lezer(): void
    {
        Carbon::setTestNow('2026-03-10 12:00:00');

        $dag = fn (string $tijd, string $zone = 'UTC'): string => trim(strip_tags(Blade::render('<x-thread-day :date="$t" :zone="$z"/>', ['t' => $tijd, 'z' => $zone])));

        $this->assertSame('Vandaag', $dag('2026-03-10 08:00:00'));
        $this->assertSame('Gisteren', $dag('2026-03-09 08:00:00'));

        // Om half twaalf 's avonds in UTC is het in Brussel al de volgende dag.
        $this->assertSame('Vandaag', $dag('2026-03-09 23:30:00', 'Europe/Brussels'));
    }

    #[Test]
    public function het_antwoordvak_onthoudt_en_draagt_zijn_waarden(): void
    {
        $html = Blade::render('<x-composer action="/antwoord" draft="gesprek.1" :values="[\'naam\' => \'O\\\'Brien\']" placeholder="Schrijf…"/>');

        $this->assertStringContainsString('data-composer-form', $html);
        $this->assertMatchesRegularExpression('/<textarea id="composer" name="body" rows="2" class="composer-input" data-autogrow\s+data-draft="gesprek.1"/', $html);
        $this->assertStringContainsString('name="_token"', $html);

        // De waarden gaan als JSON mee, ook met een aanhalingsteken erin.
        $this->assertStringContainsString('data-composer-values="{&quot;naam&quot;:&quot;O&#039;Brien&quot;}"', $html);

        // Een ander werkwoord dan POST gaat zoals Laravel het verwacht.
        $this->assertStringContainsString('name="_method" value="PUT"', Blade::render('<x-composer action="/x" method="put"/>'));
    }

    #[Test]
    public function een_avatar_houdt_zijn_kleur(): void
    {
        $nina = Blade::render('<x-avatar name="Nina Bodart" :tint="true"/>');

        $this->assertSame($nina, Blade::render('<x-avatar name="Nina Bodart" :tint="true"/>'));
        $this->assertMatchesRegularExpression('/style="background: color-mix\(in oklab, var\(--series-\d\) 16%, var\(--surface\)\); color: color-mix/', $nina);
        $this->assertStringNotContainsString('bg-gray-100', $nina);

        // Zonder tint het grijze rondje zoals altijd, en geen lege style.
        $grijs = Blade::render('<x-avatar name="Nina Bodart"/>');
        $this->assertStringContainsString('bg-gray-100', $grijs);
        $this->assertStringNotContainsString('style=', $grijs);

        // Met een foto telt de tint niet, en een eigen alt wint.
        $foto = Blade::render('<x-avatar name="Nina" src="/n.jpg" alt="" :tint="true"/>');
        $this->assertStringContainsString('alt=""', $foto);
        $this->assertStringNotContainsString('color-mix', $foto);

        $bolletje = Blade::render('<x-avatar name="Nina"><x-slot:badge title="Facebook">FB</x-slot:badge></x-avatar>');
        $this->assertStringContainsString('<span class="avatar-wrap">', $bolletje);
        $this->assertStringContainsString('<span class="avatar-badge" title="Facebook">FB</span>', $bolletje);
    }

    #[Test]
    public function een_applicatie_in_het_engels_krijgt_engelse_woorden(): void
    {
        app()->setLocale('en');
        Carbon::setTestNow('2026-03-10 12:00:00');

        $this->assertStringContainsString('Automatic reply', Blade::render('<x-message type="auto" body="Ontvangen"/>'));
        $this->assertStringContainsString('Not sent: Geen verbinding', Blade::render('<x-message type="failed" error="Geen verbinding"/>'));
        $this->assertStringContainsString('3 h', Blade::render('<x-ago time="2026-03-10 09:00:00"/>'));
        $this->assertStringContainsString('Yesterday', Blade::render('<x-thread-day date="2026-03-09 09:00:00"/>'));
        $this->assertStringContainsString('aria-label="4 messages"', Blade::render('<x-list-row href="/x" :count="4"/>'));
        $this->assertStringContainsString('Keyboard shortcuts', Blade::render('<x-shortcuts/>'));
    }

    #[Test]
    public function het_overzicht_van_de_sneltoetsen_maakt_van_ctrl_cmd_op_een_mac(): void
    {
        $html = Blade::render('<x-shortcuts :keys="[\'J\' => \'Volgende\', \'Ctrl ↵\' => \'Versturen\']"/>');

        $this->assertStringContainsString('<kbd class="kbd" >J</kbd>', $html);
        $this->assertStringContainsString('<kbd class="kbd"  data-kbd-mod >Ctrl ↵</kbd>', $html);
        $this->assertStringContainsString('id="sneltoetsen"', $html);
    }
}
