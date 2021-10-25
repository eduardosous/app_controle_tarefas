<?php

namespace App\Http\Controllers;

use Mail;
use App\Mail\NovaTarefaMail;
use App\Models\Tarefa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TarefasExport;
use PDF;

class TarefaController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $user_id = Auth::user()->id;
        $tarefas = Tarefa::where('user_id', $user_id)->paginate(10);

        return view('tarefa.index', ['tarefas' => $tarefas]);

        /*$id = Auth::user()->id;
        $name = Auth::user()->name;
        $email = Auth::user()->email;
        return "ID: $id | Nome: $name | Email: $email";*/

        /*if(Auth::check()){
            $id = Auth::user()->id;
            $name = Auth::user()->name; 
            $email = Auth::user()->email;
            return "ID: $id | Nome: $name | Email: $email";     
         }else{
            return 'Você não está logado no sistema';
         }*/


        /*if(auth()->check()){
           $id = auth()->user->id;
           $name = auth()->user->name; 
           $email = auth()->user->email;
           return "ID: $id | Nome: $name | Email: $email";     
        }else{
           return 'Você não está logado no sistema';
        }*/
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view('tarefa.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $dados = $request->all('tarefa', 'data_limite_conclusao');
        $dados['user_id'] = auth()->user()->id;

        $tarefa = Tarefa::create($dados);

        $destinatario = Auth::user()->email; //e-mail do usuário logado(autenticado)
        //Mail::to($destinatario)->send(new NovaTarefaMail($tarefa));
        return redirect()->route('tarefa.show', ['tarefa' => $tarefa->id]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Tarefa  $tarefa
     * @return \Illuminate\Http\Response
     */
    public function show(Tarefa $tarefa)
    {
        //
        return view('tarefa.show', ['tarefa' => $tarefa]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Tarefa  $tarefa
     * @return \Illuminate\Http\Response
     */
    public function edit(Tarefa $tarefa)
    {
        //
        $user_id = Auth::user()->id;

        if ($tarefa->user_id == $user_id) {
            return view('tarefa.edit', ['tarefa' => $tarefa]);
        }

        return view('acesso-negado');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Tarefa  $tarefa
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Tarefa $tarefa)
    {
        //
        /*print_r($request->all());
        echo '<hr>';
        print_r($tarefa->getAttributes());*/


        if (!$tarefa->user_id == Auth::user()->id) {

            return view('acesso-negado');
        }

        $tarefa->update($request->all());

        return redirect()->route('tarefa.show', ['tarefa' => $tarefa->id]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tarefa  $tarefa
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tarefa $tarefa)
    {
        //
        if (!$tarefa->user_id == Auth::user()->id) {

            return view('acesso-negado');
        }

        $tarefa->delete();
        return redirect()->route('tarefa.index');
    }

    public function exportacao($extensao){

        if(in_array($extensao, ['xlsx', 'csv', 'pdf'])){
            return Excel::download(new TarefasExport(), 'lista_de_tarefas.'.$extensao);
        }

        return redirect()->route('tarefa.index');

    }

    public function exportar()
    {
        $tarefas = Auth::user()->tarefas()->get();
        
        $pdf = PDF::loadView('tarefa.pdf', ['tarefas' => $tarefas]);
        
        /**
         * tipo de papel: a4, letter
         * orientação: landscape(paisagem), portrait(retrato)
         */
        $pdf->setPaper('a4', 'landscape');
        
        //return $pdf->download('lista_de_tarefas.pdf'); // baixa o pdf
        return $pdf->stream('lista_de_tarefas.pdf'); // exibe no navegador
    
    }
}
