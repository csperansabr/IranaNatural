/* Iraná Natural — Máscaras de formulário (CPF, CEP, Telefone)
 * Compartilhado entre frontend e admin.
 * Ativado por data-mask="cpf|cep|telefone" nos inputs.
 */
'use strict';

(function () {
    function aplicarMascaras() {
        document.querySelectorAll('[data-mask="cpf"]').forEach(function (input) {
            input.addEventListener('input', function () {
                var v = this.value.replace(/\D/g, '').substring(0, 11);
                if (v.length > 9)      v = v.replace(/(\d{3})(\d{3})(\d{3})(\d{0,2})/, '$1.$2.$3-$4');
                else if (v.length > 6) v = v.replace(/(\d{3})(\d{3})(\d{0,3})/, '$1.$2.$3');
                else if (v.length > 3) v = v.replace(/(\d{3})(\d{0,3})/, '$1.$2');
                this.value = v;
            });
        });

        document.querySelectorAll('[data-mask="cep"]').forEach(function (input) {
            input.addEventListener('input', function () {
                var v = this.value.replace(/\D/g, '').substring(0, 8);
                if (v.length > 5) v = v.replace(/(\d{5})(\d{0,3})/, '$1-$2');
                this.value = v;
            });
        });

        document.querySelectorAll('[data-mask="telefone"]').forEach(function (input) {
            input.addEventListener('input', function () {
                var v = this.value.replace(/\D/g, '').substring(0, 11);
                if (v.length > 10)     v = v.replace(/(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3');
                else if (v.length > 6) v = v.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
                else if (v.length > 2) v = v.replace(/(\d{2})(\d{0,5})/, '($1) $2');
                this.value = v;
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', aplicarMascaras);
    } else {
        aplicarMascaras();
    }
})();
