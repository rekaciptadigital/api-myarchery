<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Score Sheet</title>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <script type="text/javascript">
        function generateBarCode() {
            var nric = $("#text").val();
            var url = "https://api.qrserver.com/v1/create-qr-code/?data=" + nric + "&amp;size=50x50";
            $("#barcode").attr("src", url);
        }
    </script>
</head>

<body>
    <div style="padding: 8px; width: 464px; height: 707px">
        <div
            style="padding: 4px; display: flex; align-items: center; justify-content: space-between;border-radius: 8px;">
            <div style="width: 40px; height: 40px">
                <img src="./image 11.png" width="100%" height="100%" style="object-fit: cover" />
            </div>
            <div style="flex-basis: 75%">
                <span style="font-size: 12px; font-weight: 600; line-height: 14.52px">Jakarta Series I</span><br />
                <span style="font-size: 10px">Cijantung, 3 Maret 2022 - 6 Maret 2022</span>
            </div>
            <div style="width: 40px; height: 40px">
                <img src="./logo 3.png" width="100%" height="100%" style="object-fit: cover" />
            </div>
        </div>
        <div style="margin-top: 0.5rem; display: flex">
            <div style="margin-right: 2.5rem; flex-basis: 90%">
                <div>
                    <span style="font-size: 10px; font-weight: 400; margin-right: 2rem">Archer</span>
                    <span style="font-size: 12px; font-weight: 600">Aditya Priyantoro</span>
                    <hr style="border: 1px solid #e2e2e2" />
                </div>
                <div>
                    <span style="font-size: 10px; font-weight: 400; margin-right: 2.5rem">Klub</span>
                    <span style="font-size: 12px; font-weight: 600">Fast Archery</span>
                    <hr style="border: 1px solid #e2e2e2" />
                </div>
            </div>
            <div style="
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #e7edf6;
            border-radius: 8px;
            height: auto;
            width: 100%;
            flex-basis: 20%;
          ">
                <div>
                    <span>38A</span>
                </div>
            </div>
        </div>
        <div style="margin-top: 0.5rem; display: flex">
            <div style="
            background-color: #ffb420;
            flex-basis: 25%;
            border-radius: 4px;
            margin-right: 0.5rem;
            height: 23px;
            display: flex;
            justify-content: center;
            align-items: center;
          ">
                <span>Sesi I</span>
            </div>
            <div style="
            background-color: #e2e2ee;
            flex-basis: 75%;
            border-radius: 4px;
            height: 23px;
            display: flex;
            justify-content: center;
            align-items: center;
          ">
                <span>Individu Putra - Barebow - 50m</span>
            </div>
        </div>
        <div style="margin-top: 0.5rem">
            <style type="text/css">
                .tg {
                    border-collapse: collapse;
                    border-spacing: 0;
                }

                .tg td {
                    border-color: black;
                    border-style: solid;
                    border-width: 1px;
                    font-family: Arial, sans-serif;
                    font-size: 14px;
                    overflow: hidden;
                    padding: 10px 5px;
                    word-break: normal;
                }

                .tg th {
                    border-color: black;
                    border-style: solid;
                    border-width: 1px;
                    font-family: Arial, sans-serif;
                    font-size: 14px;
                    font-weight: normal;
                    overflow: hidden;
                    padding: 10px 5px;
                    word-break: normal;
                }

                .tg .tg-yj5y {
                    background-color: #efefef;
                    border-color: inherit;
                    text-align: center;
                    vertical-align: top;
                }

                .tg .tg-j4pq {
                    background-color: #efefef;
                    border-color: #000000;
                    text-align: center;
                    vertical-align: top;
                }

                .tg .tg-0pky {
                    border-color: inherit;
                    text-align: left;
                    vertical-align: top;
                }

                .tg .tg-tw5s {
                    background-color: #fe0000;
                    border-color: inherit;
                    text-align: left;
                    vertical-align: top;
                }

                .tg .tg-0lax {
                    text-align: left;
                    vertical-align: top;
                }

            </style>
            <table class="tg" style="width: 100%; height: 500px;">
                <thead>
                    <tr>
                        <th class="tg-0pky" colspan="2"></th>
                        <th class="tg-j4pq" colspan="2">1</th>
                        <th class="tg-yj5y" colspan="2">2</th>
                        <th class="tg-yj5y" colspan="2">3<br /></th>
                        <th class="tg-yj5y" colspan="2">sum</th>
                        <th class="tg-yj5y" colspan="3">Total</th>
                        <th class="tg-yj5y" colspan="2">10+x</th>
                        <th class="tg-yj5y" colspan="2">x</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="tg-yj5y" colspan="2" rowspan="2">1</td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-tw5s" colspan="3"></td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="2"></td>
                    </tr>
                    <tr>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="3"></td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="2"></td>
                    </tr>
                    <tr>
                        <td class="tg-yj5y" colspan="2" rowspan="2">2</td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-tw5s" colspan="3"></td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="2"></td>
                    </tr>
                    <tr>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="3"></td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="2"></td>
                    </tr>
                    <tr>
                        <td class="tg-yj5y" colspan="2" rowspan="2">3</td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-tw5s" colspan="3"></td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="2"></td>
                    </tr>
                    <tr>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="3"></td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="2"></td>
                    </tr>
                    <tr>
                        <td class="tg-yj5y" colspan="2" rowspan="2">4</td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-tw5s" colspan="3"></td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="2"></td>
                    </tr>
                    <tr>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="3"></td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="2"></td>
                    </tr>
                    <tr>
                        <td class="tg-yj5y" colspan="2" rowspan="2">5</td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-tw5s" colspan="3"></td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="2"></td>
                    </tr>
                    <tr>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="3"></td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="2"></td>
                    </tr>
                    <tr>
                        <td class="tg-yj5y" colspan="2" rowspan="2">6</td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-tw5s" colspan="3"></td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="2"></td>
                    </tr>
                    <tr>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="3"></td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="2"></td>
                    </tr>
                    <tr>
                        <td class="tg-0pky" colspan="10">Total</td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="2"></td>
                        <td class="tg-0pky" colspan="2"></td>

                    </tr>


                </tbody>
            </table>
        </div>
        <div style="margin-top: 0.5rem;">
            <!-- <input
          id="text"
          type="text"
          value="NRIC or Work Permit"
          style="width: 20%"
          onblur="generateBarCode();"
        /> -->

            <img id="barcode" src="https://api.qrserver.com/v1/create-qr-code/?data=HelloWorld&amp;size=100x100" alt=""
                title="HELLO" width="50" height="50" />
            <div style="display: flex; align-items: center; justify-content: space-between">
                <div style="font-size: 8px">
                    <span>Scan QR Code untuk<br /> </span>
                    <span> memulai scoring </span>
                </div>
                <div style="width: 120px; text-align: center; font-size: 8px">
                    <hr style="width: 100%" />
                    <span> Archer </span>
                </div>
                <div style="width: 120px; text-align: center; font-size: 8px">
                    <hr style="width: 100%" />
                    <span> Scorer </span>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
