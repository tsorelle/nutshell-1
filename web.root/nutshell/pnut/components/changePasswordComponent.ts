/// <reference path='../../typings/knockout/knockout.d.ts' />
/// <reference path='../js/multiSelectObservable.ts' />
namespace Peanut {
    export class changePasswordComponent {
        // observables
        errorMessage = ko.observable('');
        pwdvisible = ko.observable(false);

        password : KnockoutObservable<string>;

        // include constructor if any params used
        constructor(params: any) {
            let me = this;

            if (!params) {
                console.error('changePasswordComponent: Params not defined');
                return;
            }
            if (!params.password) {
                console.error('changePasswordComponent: Parameter "password" is required');
                return;
            }
            me.password = params.password;
            me.password('');
            me.pwdvisible(false);
        }

        toggleVisibility = () => {
            let state = this.pwdvisible();
            state = !state;
            this.pwdvisible(state);
        }
    }
}