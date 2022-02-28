<?php

namespace Tests\Feature\Event;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Tests\Feature\SyncWordsTestCase;

class EventPatchApiTest extends SyncWordsTestCase
{
    /**
     * Only authorized user can update the event
     *
     * @test
     * @return void
     */
    public function assert_only_user_can_patch_event()
    {
        $this->patchJson((route('api.event.patch', ['id' => 1])))
            ->assertStatus(401);
    }

    /**
     * Validation enabled, so correct data needs to be passed
     *
     * @test
     * @return void
     */
    public function assert_validation_enabled()
    {
        $this->actingAs($this->user())
            ->patchJson(route('api.event.patch', ['id' => 1]))
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
            ]);
    }

    /**
     * The duration between the event_start_date and event_end_date cannot exceed 12 hours.
     *
     * @test
     * @return void
     */
    public function assert_duration_between_end_date_and_start_date_cannot_exceed_12_hours()
    {
        $user = $this->userWithEvents();
        $event = $user->events()->first();

        $data = [
            'id' => $event->id,
            'event_end_date' => Carbon::parse($event->event_start_date)->addHours(13),
        ];

        $this->actingAs($user)
            ->patchJson(route('api.event.patch', ['id' => $event->id]), $data)
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
            ]);

        $data = [
            'id' => $event->id,
            'event_start_date' => Carbon::parse($event->event_end_date)->subHours(13),
        ];

        $this->actingAs($user)
            ->patchJson(route('api.event.patch', ['id' => $event->id]), $data)
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
            ]);
    }

    /**
     * Response should be {'message' => '...'}
     *
     * @test
     * @return void
     */
    public function assert_correct_json_structure_returned()
    {
        $user = $this->userWithEvents();
        $event = $user->events()->first();
        $newEventTitle = Str::random(10);

        $data = [
            'id' => $event->id,
            'event_title' => $newEventTitle,
        ];

        $this->actingAs($user)
            ->patchJson(route('api.event.patch', ['id' => $event->id]), $data)
            ->assertStatus(200)
            ->assertJsonStructure([
                'message'
            ]);

        $this->assertEquals(Event::find($event->id)->event_title, $newEventTitle);
    }
}
