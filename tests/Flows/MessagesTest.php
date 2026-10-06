<?php

declare(strict_types=1);

namespace HansDeBoeck\HansUi\Tests\Flows;

use HansDeBoeck\HansUi\Flows\Messages;
use PHPUnit\Framework\Attributes\Test;

/**
 * Zonder Laravel komt een zin er in het Nederlands uit, met de :namen
 * ingevuld, de langste eerst. Met Laravel gaat ze door de vertaler: zie
 * EditorTest.
 */
final class MessagesTest extends ZonderLaravel
{
    #[Test]
    public function vult_de_namen_in_zonder_vertaler(): void
    {
        $this->assertSame(
            'Nog in te vullen: Titel (title)',
            Messages::get('Nog in te vullen: :veld (:veldnaam)', ['veld' => 'Titel', 'veldnaam' => 'title']),
        );
    }
}
