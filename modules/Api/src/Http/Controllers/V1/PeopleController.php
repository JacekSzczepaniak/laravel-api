<?php

namespace Modules\Api\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Api\Models\Person;
use OpenApi\Attributes as OA;

final class PeopleController extends Controller
{
    #[OA\Get(
        path: "/api/v1/people",
        tags: ["People"],
        responses: [
            new OA\Response(
                response: 200,
                description: "OK",
                content: new OA\JsonContent(type: "array", items: new OA\Items(ref: "#/components/schemas/Person"))
            )
        ]
    )]
    public function index(): JsonResponse
    {
        $people = Person::query()
            ->select(['id','first_name','last_name'])
            ->orderBy('id')
            ->get();

        return response()->json($people);
    }


    #[OA\Post(
        path: "/api/v1/people",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/PersonCreateRequest")
        ),
        tags: ["People"],
        responses: [
            new OA\Response(response: 201, description: "Created",
                content: new OA\JsonContent(ref: "#/components/schemas/Person"))
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'first_name' => ['required','string','max:100'],
            'last_name'  => ['required','string','max:100'],
        ]);

        $person = Person::create([
            'first_name' => $data['first_name'],
            'last_name'  => $data['last_name'],
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        return response()->json(
            $person->only(['id','first_name','last_name']),
            201
        );
    }


    #[OA\Get(
        path: "/api/v1/people/{id}",
        tags: ["People"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "OK",
                content: new OA\JsonContent(ref: "#/components/schemas/Person"))
        ]
    )]
    public function show(Person $person): JsonResponse
    {
        return response()->json($person->only(['id','first_name','last_name']));
    }

    #[OA\Patch(
        path: "/api/v1/people/{id}",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/PersonUpdateRequest")
        ),
        tags: ["People"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "OK",
                content: new OA\JsonContent(ref: "#/components/schemas/Person"))
        ]
    )]
    public function update(Request $request, Person $person): JsonResponse
    {
        $data = $request->validate([
            'first_name' => ['sometimes','string','max:100'],
            'last_name'  => ['sometimes','string','max:100'],
        ]);

        $person->fill($data + ['updated_by' => auth()->id()])->save();

        return response()->json($person->only(['id','first_name','last_name']));
    }

    #[OA\Delete(
        path: "/api/v1/people/{id}",
        tags: ["People"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [new OA\Response(response: 204, description: "No Content")]
    )]
    public function destroy(Person $person)
    {
        $person->delete();
        return response()->noContent();
    }
}
