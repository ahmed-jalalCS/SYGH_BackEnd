<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use Spatie\PdfToImage\Pdf;
use Illuminate\Support\Facades\Log;
use App\Models\LibrarayStaff;
use App\Models\Project;
use App\Models\Department;
use App\Models\Supervisor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use function PHPUnit\Framework\isEmpty;


class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   public function index()
{
    $projects = Project::with(['evaluates', 'document']) // تحميل العلاقة
        ->where('lbraryStatus', 1)
        ->where('supervisorStatus', 1)
        ->get()
        ->map(function ($project) {
            $averageRating = $project->evaluates->avg('rating') ?? 0;

            return [
                'id' => $project->id,
                'department_id' => $project->department_id,
                'title' => $project->title,
                'description' => $project->description,
                'projectYear' => $project->projectYear,
                'average_rating' => round($averageRating, 2),
                'pathDo' => optional($project->document)->pathDo, // ✅ حماية من null
                'cover_image'=>$project->cover_image,
            ];
        });

    return response()->json($projects);
}


    public function DepartmentProjects(int $id){

        $Projects=Project::where('department_id',$id);
        return response()->json($Projects);
    }


    public function create()
    {

    }

    public function departmentId(): array
    {
        $libraraystaff = LibrarayStaff::with(['college:id', 'college.department:id,name,college_id'])
            ->where('user_id', Auth::id())
            ->first();

        if (!$libraraystaff || !$libraraystaff->college) {
            throw new \Exception('لايوجد مشاريع ');
        }

        $departmentIds = $libraraystaff->college->department->pluck('id')->toArray();

        return $departmentIds;
    }
    public function getAllProjects(Request $request)
    {
        try {

            $projects = Project::with(['department', 'supervisor'])
                ->whereIn('department_id', $this->departmentId())
                ->get();
            $formattedProjects = $projects->map(function ($project) {
                return [
                    'id' => $project->id,
                    'title' => $project->title,
                    'description' => $project->description,
                    'lbraryStatus'=> $project->lbraryStatus,
                    'projectYear' => $project->projectYear,
                    'department_name' => $project->department->name ?? null,
                    'supervisor_name' => $project->supervisor->user->name ?? null,
                ];
            });
            return response()->json([
                'success' => true,
                'data' => $formattedProjects
            ], 200);
        }   catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء المعالجة ',
                'error' => $exception->getMessage()
            ],500);
        }
    }

  public function uploadeproject(Request $request)
{
    $validator = Validator::make($request->all(), [
        'description' => 'required',
        'videoUrl' => 'nullable|url',
        'pathDo' => 'required|file|mimes:pdf,doc,docx|max:10240',
    ]);

    $validatedData = $validator->validate();

    $student = Student::where('user_id', Auth::id())
        ->where('isTemLeder', 1)
        ->first();

    if (!$student) {
        return response()->json([
            'success' => false,
            'message' => 'غير مصرح لك ',
        ]);
    }

    $project = Project::find($student->project_id);
    if (!$project) {
        return response()->json([
            'success' => false,
            'message' => 'لا يوجد مشروع بهذا الاسم',
        ], 404);
    }

    $project->update([
        'description' => $request->input('description'),
        'videoUrl' => $request->input('videoUrl'),
    ]);

    if ($request->hasFile('pathDo')) {
        $file = $request->file('pathDo');

        // حفظ المستند
        $docPath = $file->store('projects/documents', 'public');
        $fullDocUrl = asset('storage/' . $docPath);

        $project->document()->create([
            'pathDo' => $fullDocUrl,
        ]);

        // توليد صورة الغلاف
        try {
            $pdfPath = $file->getRealPath();
            $coverName = uniqid() . '.jpg';
            $coverStoragePath = storage_path('app/public/projects/covers/' . $coverName);

            // مسار Ghostscript الكامل حسب جهازك
            $gsPath = '"C:\Program Files\gs\gs10.05.0\bin\gswin64c.exe"';

            $command = "$gsPath -dNOPAUSE -dBATCH -sDEVICE=jpeg -r144 -dFirstPage=1 -dLastPage=1 -sOutputFile=\"$coverStoragePath\" \"$pdfPath\"";
            exec($command, $output, $code);

            if ($code === 0 && file_exists($coverStoragePath)) {
                $coverUrl = asset('storage/projects/covers/' . $coverName);
                $project->update([
                    'cover_image' => $coverUrl,
                ]);
            } else {
                \Log::error('Ghostscript فشل:', ['code' => $code, 'output' => $output]);
            }

        } catch (\Exception $e) {
            \Log::error('فشل توليد الغلاف: ' . $e->getMessage());
        }
    }

    return response()->json([
        'success' => true,
        'message' => 'تم الرفع بنجاح',
        'project' => $project->fresh('document'),
    ]);
}


public function store(Request $request){

    try{
            $validator=Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'videoUrl' => 'nullable|url',
                'projectYear' => 'required|date',
                'department_id' => 'required',
                'supervisor_id'=>'required',
            ]);

            $validatedData = $validator->validate();

            $Project = Project::create($validatedData);
            return response()->json(['success' => true,'message'=>'تمت الاضافة بنجاح ', 'data' => $Project], 201);

        }catch (\Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء المعالجة ',
                'error' => $e->getMessage()
            ],500);
        }
        }

     public function show(int  $id)
    {

        //supervisorStatus
        $project = Project::where('id', $id)
                            ->where('supervisorStatus', true)
                            ->where('lbraryStatus', true)
                            ->first();
           if (!$project)
           {
               return response()->json(['message' => 'لايوجد مشروع '], 404);
           }
           $projectDetails = $project->getProjectDetails();
           return response()->json($projectDetails);

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'videoUrl' => 'nullable|url',
                'projectYear' => 'nullable|date',
                'department_id' => 'nullable',
                'supervisor_id' => 'nullable',
            ]);

            $project = Project::findOrFail($id);

            $validated = $validator->validate();
            $updateUserData = [];

            if (isset($validated['title'])) {
                $updateUserData['title'] = $validated['title'];
            }
            if (isset($validated['description'])) {
                $updateUserData['description'] = $validated['description'];
            }
            if (isset($validated['videoUrl'])) {
                $updateUserData['videoUrl'] = $validated['videoUrl'];
            }
            if (isset($validated['projectYear'])) {
                $updateUserData['projectYear'] = $validated['projectYear'];
            }
            if (isset($validated['department_id'])) {
                $updateUserData['department_id'] = $validated['department_id'];
            }
            if (isset($validated['supervisor_id'])) {
                $updateUserData['supervisor_id'] = $validated['supervisor_id'];
            }

            if (!empty($updateUserData)) {
                $project->update($updateUserData);
            }

            return response()->json([
                'success' => true,
                'message' => 'تم التحديث بنجاح',
                'data' => $project,
            ], 200);


        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء المعالجة',
                'error' => $exception->getMessage()
            ], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {

            $project = Project::find($id);
            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'المشروع غير موجود',
                ]);
            }
            $department=Department::find($project->department_id);

            $libraryStaff = LibrarayStaff::where('college_id', $department->college_id)
                ->where('user_id', Auth::id())
                ->get();

            if ($libraryStaff->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مصرح لك',
                ]);
            }

            if ($project->students->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'لايمكن حذف هذه المشروع '
                ], 400);
            }

            $project->delete();
            return response()->json([
                'success' => true,
                'message' => 'تم الحذف بنجاح',
            ], 200);

        }catch (\Exception $exception){
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء الحذف ',
                'error' => $exception->getMessage()
            ],500);
        }


    }
}
