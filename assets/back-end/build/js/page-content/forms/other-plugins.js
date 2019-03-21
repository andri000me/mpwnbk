$(document).ready(function() {
    $("#default-ml").maxlength(), $("#threshold-ml").maxlength( {
        threshold: 20
    }
    ), $("#few-ml").maxlength( {
        alwaysShow: !0, threshold: 10, warningClass: "label label-info", limitReachedClass: "label label-warning"
    }
    ), $("#all-ml").maxlength( {
        alwaysShow: !0, threshold: 10, warningClass: "label label-success", limitReachedClass: "label label-danger", separator: " of ", preText: "You have ", postText: " chars remaining.", validate: !0
    }
    ), $("#textarea-ml").maxlength( {
        alwaysShow: !0
    }
    ), $("#position-ml").maxlength( {
        alwaysShow: !0, placement: "centered-right"
    }
    ), $("#postfix-ts").TouchSpin( {
        min: 0, max: 100, step: .1, decimals: 2, boostat: 5, maxboostedstep: 10, postfix: "%", buttondown_class: "btn btn-outline btn-default", buttonup_class: "btn btn-outline btn-default"
    }
    ), $("#prefix-ts").TouchSpin( {
        min: -1e9, max: 1e9, stepinterval: 50, maxboostedstep: 1e7, prefix: "$", buttondown_class: "btn btn-outline btn-default", buttonup_class: "btn btn-outline btn-default"
    }
    ), $("#vertical-ts").TouchSpin( {
        verticalbuttons: !0, buttondown_class: "btn btn-outline btn-default", buttonup_class: "btn btn-outline btn-default"
    }
    ), $("#icon-ts").TouchSpin( {
        verticalbuttons: !0, verticalupclass: "ti-plus", verticaldownclass: "ti-minus", buttondown_class: "btn btn-outline btn-default", buttonup_class: "btn btn-outline btn-default"
    }
    ), $("#empty-ts").TouchSpin( {
        buttondown_class: "btn btn-outline btn-default", buttonup_class: "btn btn-outline btn-default"
    }
    ), $("#initval-ts").TouchSpin( {
        initval: 40, buttondown_class: "btn btn-outline btn-default", buttonup_class: "btn btn-outline btn-default"
    }
    ), $("#explicitly-ts").TouchSpin( {
        initval: 40, buttondown_class: "btn btn-outline btn-default", buttonup_class: "btn btn-outline btn-default"
    }
    ), $("#small-ts").TouchSpin( {
        postfix: "a button", buttondown_class: "btn btn-outline btn-default", buttonup_class: "btn btn-outline btn-default"
    }
    ), $("#large-ts").TouchSpin( {
        postfix: "a button", buttondown_class: "btn btn-outline btn-default", buttonup_class: "btn btn-outline btn-default"
    }
    ), $("#group-ts").TouchSpin( {
        buttondown_class: "btn btn-outline btn-default", buttonup_class: "btn btn-outline btn-default"
    }
    ), new BootstrapMenu("#basic-menu", {
        actions:[ {
            name:"Action", iconClass:"fa-pencil", onClick:function() {
                toastr.info("'Action' clicked!", "Welcome to BootstrapMenu")
            }
        }
        , {
            name:"Another action", iconClass:"fa-lock", onClick:function() {
                toastr.info("'Another action' clicked!", "Welcome to BootstrapMenu")
            }
        }
        , {
            name:"A third action", iconClass:"fa-trash-o", onClick:function() {
                toastr.info("'A third action' clicked!", "Welcome to BootstrapMenu")
            }
        }
        ]
    }
    );
    var t=new Bloodhound( {
        datumTokenizer:Bloodhound.tokenizers.obj.whitespace("name"), queryTokenizer:Bloodhound.tokenizers.whitespace, prefetch: {
            url:"../assets/data/citynames.json", filter:function(t) {
                return $.map(t, function(t) {
                    return {
                        name: t
                    }
                }
                )
            }
        }
    });
    var n=new Bloodhound( {
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace("text"), queryTokenizer: Bloodhound.tokenizers.whitespace, prefetch: "../assets/data/cities.json"
    });
    n.initialize();
    var e=$("#categorizing-ti");
    e.tagsinput( {
        tagClass:function(t) {
            switch(t.continent) {
                case"Europe": return"label label-primary";
                case"America": return"label label-danger label-important";
                case"Australia": return"label label-success";
                case"Africa": return"label label-default";
                case"Asia": return"label label-warning";
                default: return"label label-default"
            }
        }
        , itemValue:"value", itemText:"text", typeaheadjs: {
            name: "cities", displayKey: "text", source: n.ttAdapter()
        }
    }
    ), e.tagsinput("add", {
        value: 1, text: "Amsterdam", continent: "Europe"
    }), e.tagsinput("add", {
        value: 4, text: "Washington", continent: "America"
    }), e.tagsinput("add", {
        value: 7, text: "Sydney", continent: "Australia"
    }), e.tagsinput("add", {
        value: 10, text: "Beijing", continent: "Asia"
    }), e.tagsinput("add", {
        value: 13, text: "Cairo", continent: "Africa"
    })
});
