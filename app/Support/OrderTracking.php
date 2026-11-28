<?php

namespace App\Support;

use App\Models\Order;
use App\Models\OrderItem;
use Carbon\CarbonInterface;

class OrderTracking
{
    /**
     * Build order and placement progress from recorded fulfillment events.
     *
     * @return array{
     *     items: list<array{item: OrderItem, websites: list<string>, published_url: ?string, status: string, steps: list<array<string, mixed>>}>,
     *     overall: array{status: string, waiting: ?string, steps: list<array<string, mixed>>}
     * }
     */
    public function build(Order $order): array
    {
        $itemTrackers = [];
        $orderAccepted = $order->accepted_at !== null || in_array($order->order_status, ['processing', 'completed'], true);

        foreach ($order->items as $item) {
            $assignmentStatus = $item->assignment?->status;
            $project = $item->project;
            $publishedUrl = trim((string) $project?->published_url);
            $publishedAt = $project?->published_at ?? $item->article?->published_at;
            $itemDelivered = $order->order_status === 'completed'
                && ($project?->status === 'completed' || $project?->verification_status === 'approved');
            $reached = [
                true,
                $orderAccepted,
                in_array($assignmentStatus, ['in_progress', 'published', 'completed'], true),
                $publishedUrl !== '',
                $itemDelivered,
            ];
            $timestamps = [
                $order->created_at,
                $order->accepted_at,
                $item->assignment?->in_progress_at,
                $publishedAt,
                $project?->completed_at,
            ];
            $waiting = [
                '',
                $order->payment_status === 'paid' ? 'Waiting for admin approval.' : 'Complete payment to begin order review.',
                $item->assignment === null ? 'Waiting for this item to be assigned.' : 'Waiting for work to start on this item.',
                $item->assignment === null || $assignmentStatus === 'assigned'
                    ? 'Waiting for assignment before publication.'
                    : 'Waiting for publication and a live URL.',
                $publishedUrl === '' ? 'Waiting for this placement to go live.' : 'Waiting for customer approval to complete this item.',
            ];
            $steps = $this->steps($reached, $timestamps, $waiting);
            $lastReachedIndex = 0;
            foreach ($reached as $index => $isReached) {
                if ($isReached) {
                    $lastReachedIndex = $index;
                }
            }

            $itemTrackers[] = [
                'item' => $item,
                'websites' => $item->selected_websites ?: array_values(array_filter([
                    $item->website?->domain ?? $item->website?->website_name,
                ])),
                'published_url' => $publishedUrl !== '' ? $publishedUrl : null,
                'status' => $lastReachedIndex === 4 ? 'Delivered' : ['Order placed', 'Accepted', 'In progress', 'On the way'][$lastReachedIndex],
                'steps' => $steps,
            ];
        }

        $itemCount = count($itemTrackers);
        $allItemsReached = [];
        for ($stageIndex = 0; $stageIndex < 5; $stageIndex++) {
            $allItemsReached[$stageIndex] = $itemCount > 0;
            foreach ($itemTrackers as $itemTracker) {
                if (! $itemTracker['steps'][$stageIndex]['reached']) {
                    $allItemsReached[$stageIndex] = false;
                    break;
                }
            }
        }

        $overallReached = [
            true,
            $orderAccepted,
            $allItemsReached[2],
            $allItemsReached[3],
            $order->order_status === 'completed' && $allItemsReached[4],
        ];
        $startedCount = count(array_filter($itemTrackers, fn (array $itemTracker): bool => $itemTracker['steps'][2]['reached']));
        $publishedCount = count(array_filter($itemTrackers, fn (array $itemTracker): bool => $itemTracker['steps'][3]['reached']));
        $deliveredCount = count(array_filter($itemTrackers, fn (array $itemTracker): bool => $itemTracker['steps'][4]['reached']));
        $overallWaiting = [
            '',
            $order->payment_status === 'paid' ? 'Waiting for admin approval.' : 'Complete payment to begin order review.',
            "Waiting for all items to start work ({$startedCount} of {$itemCount} started).",
            "Waiting for all items to have a published live URL ({$publishedCount} of {$itemCount} live).",
            "Waiting for all items to be delivered ({$deliveredCount} of {$itemCount} complete).",
        ];
        $overallTimestamps = [
            $order->created_at,
            $order->accepted_at,
            $overallReached[2] ? $this->latestItemTimestamp($itemTrackers, 2) : null,
            $overallReached[3] ? $this->latestItemTimestamp($itemTrackers, 3) : null,
            $overallReached[4] ? ($order->completed_at ?? $this->latestItemTimestamp($itemTrackers, 4)) : null,
        ];
        $overallSteps = $this->steps($overallReached, $overallTimestamps, $overallWaiting);
        $currentStep = collect($overallSteps)->first(fn (array $step): bool => $step['state'] === 'current');
        $overallStatus = $overallReached[4]
            ? 'Delivered'
            : ($overallReached[3]
                ? 'On the way'
                : ($overallReached[2]
                    ? 'In progress'
                    : ($overallReached[1] ? 'Accepted' : ($order->payment_status === 'paid' ? 'Awaiting acceptance' : 'Awaiting payment'))));

        return [
            'items' => $itemTrackers,
            'overall' => [
                'status' => $overallStatus,
                'waiting' => $currentStep['waiting'] ?? null,
                'steps' => $overallSteps,
            ],
        ];
    }

    /**
     * @param  list<bool>  $reached
     * @param  list<CarbonInterface|null>  $timestamps
     * @param  list<string>  $waiting
     * @return list<array{key: string, label: string, description: string, reached: bool, timestamp: CarbonInterface|null, waiting: string, state: string}>
     */
    private function steps(array $reached, array $timestamps, array $waiting): array
    {
        $definitions = [
            ['key' => 'placed', 'label' => 'Order placed', 'description' => 'Order submitted.'],
            ['key' => 'accepted', 'label' => 'Accepted', 'description' => 'Order approved by the admin team.'],
            ['key' => 'in_progress', 'label' => 'In progress', 'description' => 'Fulfillment work has started.'],
            ['key' => 'on_the_way', 'label' => 'On the way', 'description' => 'Placement is published and live.'],
            ['key' => 'delivered', 'label' => 'Delivered', 'description' => 'Order is complete.'],
        ];
        $currentIndex = array_search(false, $reached, true);
        $steps = [];

        foreach ($definitions as $index => $definition) {
            $steps[] = [
                ...$definition,
                'reached' => $reached[$index],
                'timestamp' => $timestamps[$index],
                'waiting' => $waiting[$index],
                'state' => $reached[$index] ? 'complete' : ($currentIndex === $index ? 'current' : 'upcoming'),
            ];
        }

        return $steps;
    }

    /**
     * @param  list<array<string, mixed>>  $itemTrackers
     */
    private function latestItemTimestamp(array $itemTrackers, int $stageIndex): ?CarbonInterface
    {
        $latestTimestamp = null;

        foreach ($itemTrackers as $itemTracker) {
            $timestamp = $itemTracker['steps'][$stageIndex]['timestamp'];
            if (! $timestamp instanceof CarbonInterface) {
                return null;
            }

            if ($latestTimestamp === null || $timestamp->getTimestamp() > $latestTimestamp->getTimestamp()) {
                $latestTimestamp = $timestamp;
            }
        }

        return $latestTimestamp;
    }
}
