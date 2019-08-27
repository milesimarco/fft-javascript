<script src="https://cdn.plot.ly/plotly-1.5.0.min.js"></script>

<div id="stage"></div>

<?php

    $handle = fopen( "input.hum", "r" );
    $i = 0;
    $RX = array();
    $passo = 8;
    $passo_counter = 0;
    $media = 0;

    if ($handle) {
        while (($line = fgets($handle)) !== false) {
            if ( $i > 20 && $i < 16385+20 ) {
                $v = explode( ';', $line);
                $RX[] = $v[0];
            }
            $i++;
        }

        fclose($handle);
    } else {
        // error opening the file.
    }
    $RX[] = 0;

    $letture = $i - 15;
?>
<script>
    var filter = 1024;

    var N = 16385;
    var NM1 = N-1;
    var ND2 = parseInt( N/2 );
    var m = parseInt( Math.log(N)/Math.log(2));
    var J = ND2;
    var tr = 0;
    var TI = 0;
    var K = 0;
    var LE = 0;
    var LE2 = 0;
    var UR = 0;
    var UI = 0;
    var SR = 0;
    var si = 0;
    var JM1 = 0;
    var IP = 0;

    var prex = [<?php echo implode( ',', $RX); ?>];
    var pimx = new Array(prex.length); 
    pimx.fill(0);

    for( i = 1; i<= N-2; i++) {
        J = parseInt( J );
        if ( i >= J ) {
            K = ND2; // 1190
        } else {
            tr = prex[J];
            TI = pimx[J];
            prex[J] = prex[i];
            pimx[J] = pimx[i];
            prex[i] = tr;
            pimx[i] = TI;
            K = ND2; // 1190
        }
        
        while ( K <= J ) { // 1200
            J = J - K;
            K = K / 2;
        }
        J = J + K; // 1240
    }
    
    for( l = 1; l<= m; l++) {
        LE = parseInt( Math.pow(2, l) );
        LE2 = parseInt( LE/2 );
        UR = 1;
        UI = 0;
        SR = Math.cos( Math.PI / LE2 );
        si = - Math.sin( Math.PI / LE2 );
        
        for( J = 1; J<=LE2; J++) { // Loop for each sub DFT
            JM1 = J -1;
            for( i = JM1; i<=NM1; i = i + LE) {

                IP = parseInt( i + LE2 );
                
                tr = prex[IP] * UR - pimx[IP] * UI // Butterfly calculation
                TI = prex[IP] * UI + pimx[IP] * UR;
                
                prex[IP] = prex[i] - tr;
                pimx[IP] = pimx[i] - TI;
                prex[i] = prex[i] + tr;
                pimx[i] = pimx[i] + TI;
            }
            tr = UR;
            UR = tr * SR - UI * si;
            UI = tr * si + UI * SR;
            

        }
    }

    var fft_exp = 0;
    var temp = 0;
    var pointsX = [];
    var pointsY = [];
    for( t = 0; t <= N/2; t++) {
        //console.log( 'im' + pimx[t] );
        //console.log( 'RX' + prex[t] );
        temp = Math.sqrt( pimx[t] * pimx[t] + prex[t] * prex[t] ) / ( filter*2 );
        console.log( "temp: " + temp)
        fft_exp[t] = temp;
        if ( t > 2 ) {
            pointsX.push( t );
            pointsY.push( parseInt( temp ) );
        }
    }
    console.log( '=== VETTORI OTTENUTI ===');
    console.log( pointsX );
    console.log( pointsY );

    var data = [
    {
        x: pointsX,
        y: pointsY,
        type: 'bar'
    }
    ];

    Plotly.newPlot('stage', data);
</script>
<body>
  
  <div id="console"></div>
</body>
