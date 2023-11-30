<?php


namespace App\Traits;

trait Status
{
    public static $status = ["REJECTED", "PENDING", "ACCEPTED"];

    protected $successStatus = 'ACCEPTED';
    protected $endingStatus = 'ACCEPTED';
    protected $failsStatus = 'REJECTED';
    protected $pendingStatus = 'PENDING';

    protected function changeStatus($status)
    {
        return $this->fill(['status' => $status])
            ->save();
    }

    public function accept()
    {
        throw_if($this->isAccepted(), trans('messages.ACCEPTED_BEFORE'));
        return $this->changeStatus("ACCEPTED");
    }

    public function reject()
    {
        throw_if($this->isRejected(), trans("messages.REJECTED_BEFORE"));
        return $this->changeStatus("REJECTED");
    }

    public function isAccepted(): bool
    {
        return isset($this->status) && $this->status === $this->successStatus;
    }

    public function isRejected(): bool
    {
        return isset($this->status) && $this->status === $this->failsStatus;
    }

    public function isPending(): bool
    {
        return isset($this->status) && $this->status === $this->pendingStatus;
    }

    /**
     * @param string $status
     * @return bool
     */
    public function checkStatus($status): bool
    {
        return isset($this->status) && $this->status === strtoupper($status);
    }

}
