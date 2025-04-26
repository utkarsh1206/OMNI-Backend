<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\UtilityRepository;
use Illuminate\Http\Request;
use App\Http\Requests\ImportDatabaseRequest;

class UtilityController extends Controller
{

    protected $utilityRepository;

    public function __construct(UtilityRepository $utilityRepository)
    {
        $this->utilityRepository = $utilityRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/utility/export",
     *     tags={"Utility"},
     *     summary="Export the database",
     *     description="Exports the entire database structure and data into a SQL file and logs the export activity.",
     *     operationId="exportDatabase",
     *     
     *      @OA\Response(
     *         response=200,
     *         description="Database exported successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="status", type="string", example="Database has been exported successfully!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Something went wrong"),
     *             @OA\Property(property="errors", type="object", example={"error": "Error description"})
     *         )
     *     )
     * )
     */
    public function export()
    {
        return $this->utilityRepository->export();
    }

    /**
     * @OA\Get(
     *     path="/api/utility/export-listing",
     *     tags={"Utility"},
     *     summary="Get Export Files Listing",
     *     description="Returns a paginated list of exported files. Supports search by file name.",
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by file name",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of records per page",
     *         required=false,
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Data fetch successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="file_name", type="string", example="db_backup_20240426.sql"),
     *                         @OA\Property(property="file_size", type="string", example="2.5 MB"),
     *                         @OA\Property(property="user_id", type="integer", example=123),
     *                         @OA\Property(property="username", type="string", example="john.doe"),
     *                         @OA\Property(property="full_file_path", type="string", example="/exports/db_backup_20240426.sql")
     *                     )
     *                 ),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *     )
     * )
     */
    public function exportUtility(Request $request)
    {
        return $this->utilityRepository->exportUtility($request);
    }

    /**
     * @OA\Post(
     *     path="/api/utility/import-database",
     *     tags={"Utility"},
     *     summary="Import Database",
     *     description="Uploads a `.sql` file and imports its contents into the database.",
     *     operationId="importDatabase",
     *     requestBody=@OA\RequestBody(
     *         required=true,
     *         content={
     *             "multipart/form-data": {
     *                 "schema": {
     *                     "type": "object",
     *                     "required": {"import_file"},
     *                     "properties": {
     *                         "import_file": {
     *                             "type": "string",
     *                             "format": "binary",
     *                             "description": "The SQL file to be imported. Must have .sql extension."
     *                         }
     *                     }
     *                 }
     *             }
     *         }
     *     ),
     *     responses={
     *         @OA\Response(
     *             response=200,
     *             description="Database imported successfully.",
     *             @OA\JsonContent(
     *                 type="object",
     *                 @OA\Property(property="status", type="boolean", example=true),
     *                 @OA\Property(property="message", type="string", example="Database imported successfully."),
     *                 @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *             )
     *         ),
     *         @OA\Response(
     *             response=422,
     *             description="Validation error (wrong file or no file uploaded)",
     *             @OA\JsonContent(
     *                 type="object",
     *                 @OA\Property(property="message", type="string", example="The import file must be a file of type: sql."),
     *                 @OA\Property(property="errors", type="object")
     *             )
     *         ),
     *         @OA\Response(
     *             response=500,
     *             description="Server error",
     *             @OA\JsonContent(
     *                 type="object",
     *                 @OA\Property(property="status", type="boolean", example=false),
     *                 @OA\Property(property="message", type="string", example="Something went wrong while importing the database.")
     *             )
     *         )
     *     }
     * )
     */
    public function import(ImportDatabaseRequest $request)
    {
        return $this->utilityRepository->import($request);
    }

    /**
     * @OA\Get(
     *     path="/api/utility/import-listing",
     *     tags={"Utility"},
     *     summary="Get Import Files Listing",
     *     description="Returns a paginated list of imported files. Supports search by file name.",
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by file name",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of records per page",
     *         required=false,
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Data fetch successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="file_name", type="string", example="imported_file_20240426.sql"),
     *                         @OA\Property(property="file_size", type="string", example="3.1 MB"),
     *                         @OA\Property(property="user_id", type="integer", example=123),
     *                         @OA\Property(property="username", type="string", example="john.doe")
     *                     )
     *                 ),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *     )
     * )
     */
    public function importUtility(Request $request)
    {
        return $this->utilityRepository->importUtility($request);
    }
}
