<?php
class ParsedownLitFootnotes extends ParsedownExtended
{
    const version = '1.0.0';

    public function __construct(array $userSettings = [])
    {
        parent::__construct($userSettings);

        # identify footnote definitions before reference definitions
        array_unshift($this->BlockTypes['['], 'LitFootnote');

        # identify footnote markers before before links
        array_unshift($this->InlineTypes['['], 'LitFootnoteMarker');
    }

    function text($text): string
    {
        $markup = parent::text($text);

        # add footnotes

        if (isset($this->DefinitionData['LitFootnote']))
        {
            $Element = $this->buildLitFootnoteElement();

            $markup .= "\n" . $this->element($Element);
        }

        return $markup;
    }

    #
    # Blocks
    #

    #
    # Literature Footnote

    protected function blockLitFootnote($Line)
    {
        if (preg_match('/^\s*\[#(.+?)\]:[ ]?(.*)$/', $Line['text'], $matches))
        {
            $Block = array(
                'label' => $matches[1],
                'text' => $matches[2],
                'hidden' => true,
            );

            return $Block;
        }
    }

    protected function blockLitFootnoteContinue($Line, $Block)
    {
        if ($Line['text'][0] === '[' and preg_match('/^\[\#(.+?)\]:/', $Line['text']))
        {
            return;
        }

        if (isset($Block['interrupted']))
        {
            if ($Line['indent'] >= 4)
            {
                $Block['text'] .= "\n\n" . $Line['text'];

                return $Block;
            }
        }
        else
        {
            $Block['text'] .= "\n" . $Line['text'];

            return $Block;
        }
    }

    protected function blockLitFootnoteComplete($Block)
    {
        error_log("complet");
        $this->DefinitionData['LitFootnote'][$Block['label']] = array(
            'text' => $Block['text'],
            'count' => null,
            'number' => null,
        );

        return $Block;
    }

    #
    # Inline Elements
    #

    #
    # Literature Footnote Marker

    protected function inlineLitFootnoteMarker($Excerpt)
    {
        if (preg_match('/^\[\#(\w+?)\]\[(\w{0,})\]/', $Excerpt['text'], $matches))
        {
            return $this->processLitFootnote($matches[1], strlen($matches[0]), $matches[2]);
        }
        else if (preg_match('/^\[(\w{0,})\]\[\#(\w+?)\]/', $Excerpt['text'], $matches))
        {
            return $this->processLitFootnote($matches[2], strlen($matches[0]), $matches[1]);
        }
    }

    private function processLitFootnote(string $name, int $extent, ?string $reference): ?array {
        if ( ! isset($this->DefinitionData['LitFootnote'][$name]))
        {
            error_log("Def data not found for ".$name);
            return null;
        }

        $this->DefinitionData['LitFootnote'][$name]['count'] ++;

        if ( ! isset($this->DefinitionData['LitFootnote'][$name]['number']))
        {
            $this->DefinitionData['LitFootnote'][$name]['number'] = ++ $this->footnoteCount; # » &
        }

        $Element = array(
            'name' => 'sup',
            'attributes' => array('id' => 'fnref'.$this->DefinitionData['LitFootnote'][$name]['count'].':'.$name),
            'handler' => 'element',
            'text' => array(
                'name' => 'a',
                'attributes' => array('href' => '#fn:'.$name, 'class' => 'footnote-ref'),
                'text' => $this->DefinitionData['LitFootnote'][$name]['number'],
            ),
        );

        return array(
            'extent' => $extent,
            'element' => $Element,
        );
    }

    private $footnoteCount = 0;

    #
    # Util Methods
    #

    protected function buildLitFootnoteElement()
    {
        $Element = array(
            'name' => 'div',
            'attributes' => array('class' => 'footnotes'),
            'handler' => 'elements',
            'text' => array(
                array(
                    'name' => 'hr',
                ),
                array(
                    'name' => 'ol',
                    'handler' => 'elements',
                    'text' => array(),
                ),
            ),
        );

        uasort($this->DefinitionData['LitFootnote'], 'self::sortFootnotes');

        foreach ($this->DefinitionData['LitFootnote'] as $definitionId => $DefinitionData)
        {
            if ( ! isset($DefinitionData['number']))
            {
                continue;
            }

            $text = $DefinitionData['text'];

            $text = parent::text($text);

            $numbers = range(1, $DefinitionData['count']);

            $backLinksMarkup = '';

            foreach ($numbers as $number)
            {
                $backLinksMarkup .= ' <a href="#fnref'.$number.':'.$definitionId.'" rev="footnote" class="footnote-backref">&#8617;</a>';
            }

            $backLinksMarkup = substr($backLinksMarkup, 1);

            if (substr($text, - 4) === '</p>')
            {
                $backLinksMarkup = '&#160;'.$backLinksMarkup;

                $text = substr_replace($text, $backLinksMarkup.'</p>', - 4);
            }
            else
            {
                $text .= "\n".'<p>'.$backLinksMarkup.'</p>';
            }

            $Element['text'][1]['text'] []= array(
                'name' => 'li',
                'attributes' => array('id' => 'fn:'.$definitionId),
                'rawHtml' => "\n".$text."\n",
            );
        }

        return $Element;
    }

    protected function sortFootnotes($A, $B) # callback
    {
        return $A['number'] - $B['number'];
    }
}
