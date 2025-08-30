<?php

namespace Modules\Api\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Modules\Api\Mail\WelcomePersonMail;
use Modules\Api\Models\Person;
use Modules\Api\Models\PersonContact;
use OpenApi\Attributes as OA;

final class PeopleContactsController extends Controller
{
    #[OA\Get(
        path: "/api/v1/people/{person}/contacts",
        tags: ["Contacts"],
        parameters: [
            new OA\Parameter(name: "person", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "OK",
                content: new OA\JsonContent(type: "array", items: new OA\Items(ref: "#/components/schemas/PersonContact")))
        ]
    )]
    public function index(Person $person): JsonResponse
    {
        return response()->json(
            $person->contacts()->select(['id','type','value','is_primary'])->orderBy('id')->get()
        );
    }

    #[OA\Post(
        path: "/api/v1/people/{person}/contacts",
        requestBody: new OA\RequestBody(required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/ContactCreateRequest")
        ),
        tags: ["Contacts"],
        parameters: [
            new OA\Parameter(name: "person", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 201, description: "Created",
                content: new OA\JsonContent(ref: "#/components/schemas/PersonContact"))
        ]
    )]
    public function store(Request $request, Person $person): JsonResponse
    {
        $data = $request->validate([
            'type'       => ['required','string','max:32'],
            'value'      => [
                'required','string','max:255',
                Rule::unique('person_contacts', 'value')
                    ->where(fn ($q) => $q
                        ->where('person_id', $person->id)
                        ->where('type', $request->input('type'))
                        ->whereNull('deleted_at') // ignoruj soft-deleted
                    ),
            ],
            'is_primary' => ['sometimes','boolean'],
        ]);

        $contact = $person->contacts()->create([
            'type'       => $data['type'],
            'value'      => $data['value'],
            'is_primary' => $data['is_primary'] ?? false,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        return response()->json($contact->only(['id','type','value','is_primary']), 201);
    }


    #[OA\Patch(
        path: "/api/v1/people/{person}/contacts/{contact}",
        requestBody: new OA\RequestBody(required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/ContactUpdateRequest")
        ),
        tags: ["Contacts"],
        parameters: [
            new OA\Parameter(name: "person", in: "path", required: true, schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "contact", in: "path", required: true, schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(response: 200, description: "OK",
                content: new OA\JsonContent(ref: "#/components/schemas/PersonContact"))
        ]
    )]
    public function update(Request $request, Person $person, PersonContact $contact): JsonResponse
    {
        $data = $request->validate([
            'type'       => ['sometimes','string','max:32'],
            'value'      => [
                'sometimes','string','max:255',
                Rule::unique('person_contacts', 'value')
                    ->ignore($contact->id)
                    ->where(fn ($q) => $q
                        ->where('person_id', $person->id)
                        ->where('type', $request->input('type', $contact->type))
                        ->whereNull('deleted_at')
                    ),
            ],
            'is_primary' => ['sometimes','boolean'],
        ]);

        $contact->fill($data + ['updated_by' => auth()->id()])->save();

        return response()->json($contact->only(['id','type','value','is_primary']));
    }


    #[OA\Delete(
        path: "/api/v1/people/{person}/contacts/{contact}",
        tags: ["Contacts"],
        parameters: [
            new OA\Parameter(name: "person", in: "path", required: true, schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "contact", in: "path", required: true, schema: new OA\Schema(type: "integer")),
        ],
        responses: [new OA\Response(response: 204, description: "No Content")]
    )]
    public function destroy(Person $person, PersonContact $contact)
    {
        $contact->delete();
        return response()->noContent();
    }

    #[OA\Post(
        path: "/api/v1/people/{person}/emails/send-welcome",
        security: [["sanctum" => []]],
        tags: ["Contacts", "Email"],
        parameters: [
            new OA\Parameter(name: "person", in: "path", required: true, schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(
                response: 202,
                description: "Accepted",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "sent", type: "integer", example: 2),
                ])
            )
        ]
    )]
    public function sendWelcome(int $id): JsonResponse
    {
        $person = Person::findOrFail($id);

        $emails = $person->contacts()
            ->where('type', 'email')
            ->pluck('value')
            ->all();

        if (!$emails) {
            return response()->json(['sent' => 0], 202);
        }

        $fullName = trim($person->first_name . ' ' . $person->last_name);

        foreach ($emails as $email) {
            Mail::to($email)->send(new WelcomePersonMail($fullName));
        }

        return response()->json(['sent' => count($emails)], 202);
    }

}
