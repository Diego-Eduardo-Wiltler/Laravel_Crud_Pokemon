<?php

namespace App\Services;

use App\Models\Pokemon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class PokemonService
{
    public function getPokemon()
    {
        $pokemons = Pokemon::orderBy('id', 'Asc')->get();
        return [
            'status' => true,
            'pokemons' => $pokemons,
        ];
    }
    public function getById($id)
    {
        $pokemon = Pokemon::findOrFail($id);
        return [
            'status' => true,
            'pokemon' => $pokemon
        ];
    }

    public function storePokemon(array $data)
    {
        DB::beginTransaction();
        try {
            $pokemon = Pokemon::create($data);
            DB::commit();
            return [
                'status' => true,
                'pokemon' => $pokemon,
                'message' => 'cadastrado',
            ];
        } catch (Exception $e) {
            return [
                'status' => false,
                'message' => 'n cadastrado',
            ];
        }
    }

    public function battlePokemon($id1, $id2)
    {

        $pokemon1 = null;
        $pokemon2 = null;
        try {

            $pokemon1 = Pokemon::findOrFail($id1);
            $pokemon2 = Pokemon::findOrFail($id2);


            do {
                $pokemon1->vida_atual -= $pokemon2->ataque;
                $pokemon2->vida_atual -= $pokemon1->ataque;
            } while ($pokemon1->vida_atual > 0 && $pokemon2->vida_atual > 0);


            $pokemon1->save();
            $pokemon2->save();


            if ($pokemon1->vida_atual > 0 && $pokemon2->vida_atual <= 0) {
                $vencedor = $pokemon1;
            } elseif ($pokemon2->vida_atual > 0 && $pokemon1->vida_atual <= 0) {
                $vencedor = $pokemon2;
            } else {
                return [
                    "message" => "A batalha terminou em empate!",
                    "status" => false,
                ];
            }

            $message = 'O Pokémon vencedor é';

            $response = [
                "message" => $message,
                "status" => true,
                "pokemon" => $vencedor
            ];
        } catch (ModelNotFoundException | Exception $e) {
            $response = [
                'status' => false,
                'produtos' => null,
                'message' => 'Produto não encontrado',
            ];
        }
        return $response;
    }

    public function storeHealing($id)
    {
        $pokemon = Pokemon::find($id);

        $vidaRecuperada = $pokemon->vida_atual - $pokemon->vida;
        $pokemon->vida = $pokemon->vida_atual;
        $pokemon->save();

        return ([
            'message' => 'pokemon curado',
            'status' => true,
            'pokemon' => [
                'nome' => $pokemon->nome,
                'vida_recuperada' => $vidaRecuperada,
                'vida_total' => $pokemon->vida,
            ],
        ]);
    }

    public function storeRound($id1, $id2)
    {
        $pokemonATK = Pokemon::find($id1);
        $pokemonDEF = Pokemon::find($id2);

        $pokemonMitiga = $pokemonDEF->defesa / $pokemonATK->ataque * 100;
        $danoCausado = $pokemonATK->ataque;
        $defesaTexto = '';

        if ($pokemonMitiga < 30) {
            $defesaTexto = ' não conseguiu se defender do ataque ';
        } elseif ($pokemonMitiga >= 30 && $pokemonMitiga < 50) {
            $danoCausado /= 1.2;
            $defesaTexto = ' se defendeu um pouco do ataque ';
        } elseif ($pokemonMitiga >= 50 && $pokemonMitiga < 100) {
            $danoCausado /= 1.5;
            $defesaTexto = ' se defendeu bem do ataque ';
        } elseif ($pokemonMitiga == 100) {
            $danoCausado /= 2;
            $defesaTexto = ' se defendeu efetivamente do ataque ';
        } elseif ($pokemonMitiga >= 130) {
            $danoCausado /= 3;
            $defesaTexto = ' se defendeu extremamente bem do ataque ';
        } else {
            return [
                'status' => false,
                'messages' => [
                    'Algo deu errado durante a batalha. Verifique os valores.',
                ],
            ];
        }
        $danoCausado = ceil($danoCausado);
        $pokemonDEF->vida -= $danoCausado;
        $pokemonDEF->save();

        return [
            'status' => true,
            'pokemonATK' => $pokemonATK,
            'pokemonDEF' => $pokemonDEF,
            'danoCausado' => $danoCausado,
            'defesaTexto' => $defesaTexto,

        ];
    }



    public function updatePokemon(array $data, $id)
    {
        $pokemon = Pokemon::findOrFail($id);
        DB::beginTransaction();
        try {
            $pokemon->update($data);
            DB::commit();
            return [
                'status' => true,
                'pokemon' => $pokemon,
                'message' => 'atualizado',
            ];
        } catch (Exception $e) {
            return [
                'status' => false,
                'message' => 'n atualizado',
            ];
        }
    }

    public function deletePokemon($id)
    {
        $pokemon = Pokemon::findOrFail($id);
        try {
            $pokemon->delete($id);
            return [
                'status' => true,
                'pokemon' => $pokemon,
                'message' => 'excluido',
            ];
        } catch (Exception $e) {
            return [
                'status' => false,
                'message' => 'n excluido',
            ];
        }
    }
}
