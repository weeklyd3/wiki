<?php 
class logEntry {
    public function __construct(int $performer, ?int $targetUser, ?string $relatedPage, string $type, string $comment, string $reason) {
        $this->performer = $performer;
        $this->page = $relatedPage;
        $this->targetUser = $targetUser;
        $this->type = $type;
        $this->comment = $comment;
        $this->reason = $reason;
        $this->time = time();

        $log = json_decode(file_get_contents(__DIR__ . "/log.json"));
        array_unshift($log, $this);
        fwrite(fopen(__DIR__ . "/log.json", "w+"), json_encode($log));
    }
}
function queryLog(array $types = array(), ?string $relatedPage, bool $noPage = false) {
    $log = json_decode(file_get_contents(__DIR__ . "/log.json"));
    return array_filter($log, function($entry) use ($types, $noPage, $relatedPage) {
        if (count($types) !== 0) {
            if (!in_array($entry->type, $types)) return false;
        }
        if ($noPage && isset($entry->page)) return false;
        if (!isset($relatedPage) && !$noPage) return false;
        if (!$noPage && ($relatedPage !== $entry->page)) return false;
        return true;
    });
}