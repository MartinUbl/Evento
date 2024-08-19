<?php

namespace App\Misc;

final class CSVResponse implements \Nette\Application\Response
{
	private $fileName;
	private $rowStrings;
	private $delimiter;

	public function __construct(string $fileName, iterable $rowStrings)
	{
		$this->fileName = $fileName;
		$this->rowStrings = $rowStrings;
	}

	public function send(\Nette\Http\IRequest $request, \Nette\Http\IResponse $response): void
	{
		$response->setContentType('text/csv', 'utf-8');
		$response->setHeader('Content-Description', 'File Transfer');

		$tmp = str_replace('"', "'", $this->fileName);
		$response->setHeader(
			'Content-Disposition',
			"attachment; filename=\"$tmp\"; filename*=utf-8''" . rawurlencode($this->fileName)
		);

		$bom = true;
		$fd = fopen('php://output', 'wb');

		foreach ($this->rowStrings as $row) {
			if ($bom) {
				// Excel encoding
				fputs($fd, "\xEF\xBB\xBF");
				$bom = false;
			}

			fputs($fd, $row."\n");
		}

		fclose($fd);
	}
}