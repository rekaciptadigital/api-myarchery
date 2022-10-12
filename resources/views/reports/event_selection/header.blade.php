<!DOCTYPE html>
<html>

<head>
    <script>
        function subst() {
            var vars = {};
            var query_strings_from_url = document.location.search.substring(1).split('&');
            for (var query_string in query_strings_from_url) {
                if (query_strings_from_url.hasOwnProperty(query_string)) {
                    var temp_var = query_strings_from_url[query_string].split('=', 2);
                    vars[temp_var[0]] = decodeURI(temp_var[1]);
                }
            }
            var css_selector_classes = ['page', 'frompage', 'topage', 'webpage', 'section', 'subsection', 'date',
                'isodate', 'time', 'title', 'doctitle', 'sitepage', 'sitepages'
            ];
            for (var css_class in css_selector_classes) {
                if (css_selector_classes.hasOwnProperty(css_class)) {
                    var element = document.getElementsByClassName(css_selector_classes[css_class]);
                    for (var j = 0; j < element.length; ++j) {
                        element[j].textContent = vars[css_selector_classes[css_class]];
                    }
                }
            }
        }
    </script>
</head>

<body style="border:0; margin: 0;" onload="subst()">
    <!-- <table style="border-bottom: 1px solid black; width: 100%">
        <tr>
            <td class="section"></td>
            <td style="text-align:right">
                Page <span class="page"></span> of <span class="topage"></span>
            </td>
        </tr>
    </table> -->
    <table style="border-bottom: 1px solid black; width: 100%">
        <tbody>
            <tr style="height: 40px;">
                <td style="width: 10%; height: 50px;" rowspan="2">
                    <img src="{{ $logo_event }}" alt="" width="80%">
                </td>
                <td style="width: 10%; height: 50px;" rowspan="2">{!! $logo_archery !!}</td>
                <td style="width: 42%; height: 50px; ">
                    <p style="text-align: left; font-size: 18pt; font-family: helvetica;">
                        <strong><span style="font-size: 30px;">{{ $event_name_report }}</span></strong> <br /><br />
                        {{ $event_location_report }}<br />
                        {{ $event_date_report }}
                    </p>
                </td>
            </tr>
        </tbody>
    </table>
</body>

</html>