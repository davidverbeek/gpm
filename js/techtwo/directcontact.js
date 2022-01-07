/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

function showDCDialog(event){
    var targetDC = $(Event.findElement(event || window.event, 'a'));
    //var targetDC = $(event.target.id);
    targetDC.siblings().each(function(sibling) {
        if(sibling.DCWindow && sibling.DCWindow.visible) {
            sibling.DCWindow.hide();
        }
    });
    if(targetDC.DCWindow) {
        $('directcontactTelForm') && $('directcontactTelForm').setStyle({display: ''});
        $('directcontactTelResult') && $('directcontactTelResult').setStyle({display: 'none'});
        $('directcontactTelSpinner') && $('directcontactTelSpinner').setStyle({display: 'none'});
        $('directcontactMailForm') && $('directcontactMailForm').setStyle({display: ''});
        $('directcontactMailResult') && $('directcontactMailResult').setStyle({display: 'none'});
        $('directcontactMailSpinner') && $('directcontactMailSpinner').setStyle({display: 'none'});
        if(targetDC.DCWindow.visible) {
            targetDC.DCWindow.center();
        } else {
            targetDC.DCWindow.show().focus();
        }
    } else {
        // if(targetDC.id == 'directcontactChat') {
        //     $('directcontactChatSpinner').setStyle({display: 'block'});

        //     var newIframe = new Element('iframe',{style: "width:240px; height:310px; border:0;",
        //         src: '/skin/frontend/default/default/directcontactchat.php?zopim=' + zopimId})
        //     $('directcontactChatTarget').insert(newIframe);
        //     new PeriodicalExecuter(function(pe) {
        //         if(typeof newIframe.contentWindow.$zopim != 'undefined') { // IE might use newIframe.document
        //             $zopim = newIframe.contentWindow.$zopim;
        //             pe.stop();
        //             //$$('head').fire('dom:loaded', 'foobarbaz
        //             $zopim.livechat.set({
        //                     language: 'nl',
        //                     name: 'Bezoeker',
        //                     email: 'bezoeker@example.com',
        //                     onStatus: function(s) {
        //                         $('directcontactChatSpinner').setStyle({display: 'none'});
        //                          }
        //                 });
        //             $zopim.livechat.window.show();
        //             $('directcontactChatSpinner').setStyle({display: 'none'});
        //             //$zopim.livechat.bubble.setTitle('Test title');
        //             //$zopim.livechat.bubble.setText('Test text');
        //             //$zopim.livechat.bubble.show();
        //         }
        //     }, 5);
        // }
        targetDC.DCWindow = new UI.Window({theme: 'bluelighting'
                                         , width: 244
                                         , height:357
                                         , resizable: false
                                         , close: function () {
                                                $('directcontactTelResult') && $('directcontactTelResult').setStyle({display: 'none'});
                                                $('directcontactTelSpinner') && $('directcontactTelSpinner').setStyle({display: 'none'});
                                                $('directcontactMailResult') && $('directcontactMailResult').setStyle({display: 'none'});
                                                $('directcontactMailSpinner') && $('directcontactMailSpinner').setStyle({display: 'none'});
                                                this.hide();
                                            }
                                        })
                                .center()
                                //.setFooter('test')
                                .setHeader(zopimTitle)
                                .setContent($(targetDC.id + 'Box'))
                                .show().focus();
        targetDC.DCWindow.element.setStyle({zIndex:'1001'});
        new Effect.toggle(targetDC.id + 'Box', 'appear');
        
//        e.target.DCWindow = showDCDialog('directcontactBoxChat');
    }


    //return newDialog;
    return false;
}
function dcShowError(message, parentId) {
    var errorEl = $(parentId + 'Error');
    errorEl.update(message);
    Effect.Appear(errorEl);
}
function dcHideError(parentId) {
    Effect.Fade(parentId + 'Error');
}

function dcCheckPhone(el) {
    var phone = el.getValue().strip();
    if(phone.length == 0) {
        dcShowError('Vul alstublieft een telefoonnummer in', el.id);
        return false;
    }
    var phonePattern = /^[-+ 0-9]{8,15}$/;
    if(phonePattern.test(phone)) {
        dcHideError(el.id);
        return true;
    } else {
        dcShowError('Ongeldig telefoonnummer', el.id);
        return false;
    }
}

function dcCheckEmail(el) {
    var email = el.getValue().strip();
    if(email.length == 0) {
        dcShowError('Vul alstublieft een e-mailadres in', el.id);
        return false;
    }
    var emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;  
    if(emailPattern.test(email)) {
        dcHideError(el.id);
        return true;
    } else {
        dcShowError('Ongeldig email adres', el.id);
        return false;
    }
}
function dcCheckName(el) {
    if(el.getValue().strip().length == 0) {
        dcShowError('Vul alstublieft een naam in', el.id);
        return false;
    }
    dcHideError(el.id);
    return true;
}
function dcCheckComment(el) {
    if(el.getValue().strip().length == 0) {
        dcShowError('Vul alstublieft een vraag in', el.id);
        return false;
    }
    dcHideError(el.id);
    return true;
}


document.observe("dom:loaded", function() {
    if (!document.getElementById('directcontactTel')) return true;
    document.getElementById('directcontactTel').onclick = showDCDialog;
    document.getElementById('directcontactMail').onclick = showDCDialog;
 //   document.getElementById('directcontactChat').onclick = showDCDialog;
    Event.observe('directcontactTelForm' , 'submit', function(e) {
        Event.stop(e);
        return false;
    });
    Event.observe('directcontactTelSubmit' , 'click', function(e) {
        Event.stop(e);
        $('directcontactTelSpinner').setStyle({display: 'block'});
        //$('directcontactTelForm').setStyle({display: 'none'});
        var form = this.form;
        // Check fields, returns false if there is an error, sets its own error message
        var phoneResponse = dcCheckPhone(form.phoneNumber);
        var nameResponse = dcCheckName(form.phoneName);
        var commentResponse = dcCheckComment(form.phoneComment);
        // If all responses are true, then everything is alright, submit the form
        if(phoneResponse && nameResponse && commentResponse) {
            var serial = form.serialize(true); serial['phoneSubmit'] = 1;
            //new Ajax.Request('/en/directcontact/sendemail/', {method: 'post'
            new Ajax.Request('/directcontact/sendemail/', {method: 'post'
                    , onSuccess: function(transport) {
                        if (transport.responseText.match(/success/)) {
                            $$('.dcInput').each(function(el) {
                                el.setValue('');
                            });
                            $('directcontactTelResult').update('Uw contactverzoek is verzonden. <br /> U kunt dit dialoogvenster nu sluiten.');
                        }
                        else {
                            $('directcontactTelResult').update('Er is helaas een onbekende fout opgetreden. <br /> Gebruik alsjeblieft de contactpagina.');
                        }
                        $('directcontactTelResult').setStyle({display: 'block'});
                        $('directcontactTelSpinner').setStyle({display: 'none'});
                    }
                    , onFailure: function(transport) {
                        $('directcontactTelResult').update('Er is helaas een onbekende fout opgetreden. <br /> Gebruik alsjeblieft de contactpagina.');
                        $('directcontactTelResult').setStyle({display: 'block'});
                        $('directcontactTelSpinner').setStyle({display: 'none'});
                    }
                    , parameters: serial
                });
            return false;
        } else {
            $('directcontactTelSpinner').setStyle({display: 'none'});
        }
        return false;
    });
    Event.observe('directcontactMailSubmit' , 'click', function(e) {
        Event.stop(e);
        $('directcontactMailSpinner').setStyle({display: 'block'});
        //$('directcontactMailForm').setStyle({display: 'none'});
        var form = this.form;
        // Check fields, returns false if there is an error, sets its own error message
        var emailResponse = dcCheckEmail(form.mailEmail);
        var nameResponse = dcCheckName(form.mailName);
        var commentResponse = dcCheckComment(form.mailComment);
        // If all responses are true, then everything is alright, submit the form
        if(emailResponse && nameResponse && commentResponse) {
            
            var serial = form.serialize(true); serial['mailSubmit'] = 1;
            //new Ajax.Request('/en/directcontact/sendemail/', {method: 'post'
            
            new Ajax.Request('/directcontact/sendemail/', {method: 'post'
                    , onSuccess: function(transport) {
                        if (transport.responseText.match(/success/)) {
                            $$('.dcInput').each(function(el) {
                                el.setValue('');
                            });
                            $('directcontactMailResult').update('Uw contactverzoek is verzonden. <br /> U kunt dit dialoogvenster nu sluiten.');
                           
                        }
                        else {
                            $('directcontactMailResult').update('Er is helaas een onbekende fout opgetreden. <br /> Gebruik alsjeblieft de contactpagina.');
                        }
                        $('directcontactMailResult').setStyle({display: 'block'});
                        $('directcontactMailSpinner').setStyle({display: 'none'});
                    }
                    , onFailure: function(transport) {
                        $('directcontactMailResult').update('Er is helaas een onbekende fout opgetreden. <br /> Gebruik alsjeblieft de contactpagina.');
                        $('directcontactMailResult').setStyle({display: 'block'});
                        $('directcontactMailSpinner').setStyle({display: 'none'});
                    }
                    , parameters: serial
                });
            return false;
        } else {
            $('directcontactMailSpinner').setStyle({display: 'none'});
        }
        return false;
    });
    /*Event.observe('directcontactChatSubmit' , 'click', function(e) {
        Event.stop(e);
        var form = this.form;
        // Check fields, returns false if there is an error, sets its own error message
        var emailResponse = dcCheckEmail(form.chatEmail);
        var nameResponse = dcCheckName(form.chatName);
        var commentResponse = dcCheckComment(form.chatComment);
        // If all responses are true, then everything is alright, submit the form
        if(emailResponse && nameResponse && commentResponse) {
            var serial = form.serialize(true);
            new Ajax.Request('/directcontact/sendemail/', {method: 'post'
                    , onSuccess: function(transport) {
                    }
                    , onFailure: function(transport) {
                    }
                    , parameters: serial
                });
            return false;
        }
        return false;
    });*/
});
