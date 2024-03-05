<?php

class pluginParsedownExtraCombined extends Plugin {

	private function parse($content)
	{
		require_once($this->phpPath().'vendors/ParsedownExtra.php');
    require_once($this->phpPath().'vendors/ParsedownTOCExtension.php');
		$Parsedown = new ParsedownToC();
		return $Parsedown->text($content);
	}

	public function beforeSiteLoad()
	{
		if ($GLOBALS['WHERE_AM_I']=='page') {
			$content = $this->parse($GLOBALS['page']->contentRaw());
			$GLOBALS['page']->setField('content', $content);
		} else {
			foreach ($GLOBALS['content'] as $key=>$page)  {
				$content = $this->parse($page->contentRaw());
				$GLOBALS['content'][$key]->setField('content', $content);
			}
			$GLOBALS['page'] = $GLOBALS['content'][0];
		}
	}
}
