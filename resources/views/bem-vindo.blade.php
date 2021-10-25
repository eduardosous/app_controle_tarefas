Site da aplicação

@auth

    <h1>Usuário Autenticado</h1>
    <p>{{ Auth::user()->id }}</p>
    <p>{{ Auth::user()->name }}</p>
    <p>{{ Auth::user()->email }}</p>

@endauth

@guest
  Olá visitante, tudo bem?   
@endguest
