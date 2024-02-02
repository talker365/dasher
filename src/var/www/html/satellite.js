/*!
 * satellite-js v4.0.0
 * (c) 2013 Shashwat Kandadai and UCSC
 * https://github.com/shashwatak/satellite-js
 * License: MIT
 */

! function (o, t)
{
    "object" == typeof exports && "undefined" != typeof module ? module.exports = t() : "function" == typeof define && define.amd ? define(t) : (o = o || self).satellite = t()
}(this, function ()
{
    "use strict";
    var Ho = Math.PI,
        Yo = 2 * Ho,
        m = Ho / 180,
        t = 180 / Ho,
        o = 398600.5,
        Ao = 6378.137,
        Bo = 60 / Math.sqrt(Ao * Ao * Ao / o),
        zo = Ao * Bo / 60,
        p = 1 / Bo,
        Co = .00108262998905,
        s = -253215306e-14,
        Uo = -161098761e-14,
        Do = s / Co,
        Jo = 2 / 3;

    function l(o, t)
    {
        for (var s = [31, o % 4 == 0 ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31], e = Math.floor(t), a = 1, n = 0; n + s[a - 1] < e && a < 12;) n += s[a - 1], a += 1;
        var d = a,
            i = e - n,
            r = 24 * (t - e),
            c = Math.floor(r);
        r = 60 * (r - c);
        var h = Math.floor(r);
        return {
            mon: d,
            day: i,
            hr: c,
            minute: h,
            sec: 60 * (r - h)
        }
    }

    function r(o, t, s, e, a, n)
    {
        var d = 6 < arguments.length && void 0 !== arguments[6] ? arguments[6] : 0;
        return 367 * o - Math.floor(7 * (o + Math.floor((t + 9) / 12)) * .25) + Math.floor(275 * t / 9) + s + 1721013.5 + ((d / 6e4 + n / 60 + a) / 60 + e) / 24
    }

    function x(o, t, s, e, a, n, d)
    {
        if (o instanceof Date)
        {
            var i = o;
            return r(i.getUTCFullYear(), i.getUTCMonth() + 1, i.getUTCDate(), i.getUTCHours(), i.getUTCMinutes(), i.getUTCSeconds(), i.getUTCMilliseconds())
        }
        return r(o, t, s, e, a, n, d)
    }

    function Ro(o, t)
    {
        var s, e, a, n, d, i, r, c, h, m, p, l, x, g, M, f, u, z, v = o.e3,
            y = o.ee2,
            b = o.peo,
            q = o.pgho,
            w = o.pho,
            T = o.pinco,
            j = o.plo,
            E = o.se2,
            F = o.se3,
            L = o.sgh2,
            A = o.sgh3,
            C = o.sgh4,
            U = o.sh2,
            D = o.sh3,
            R = o.si2,
            S = o.si3,
            I = o.sl2,
            _ = o.sl3,
            k = o.sl4,
            O = o.t,
            P = o.xgh2,
            Z = o.xgh3,
            G = o.xgh4,
            H = o.xh2,
            Y = o.xh3,
            B = o.xi2,
            J = o.xi3,
            K = o.xl2,
            N = o.xl3,
            Q = o.xl4,
            V = o.zmol,
            W = o.zmos,
            X = t.init,
            $ = t.opsmode,
            oo = t.ep,
            to = t.inclp,
            so = t.nodep,
            eo = t.argpp,
            ao = t.mp;
        z = W + 119459e-10 * O, "y" === X && (z = W), u = z + .0335 * Math.sin(z);
        var no = E * (r = .5 * (g = Math.sin(u)) * g - .25) + F * (c = -.5 * g * Math.cos(u)),
            io = R * r + S * c,
            ro = I * r + _ * c + k * g,
            co = L * r + A * c + C * g,
            ho = U * r + D * c;
        return z = V + .00015835218 * O, "y" === X && (z = V), u = z + .1098 * Math.sin(z), h = no + (y * (r = .5 * (g = Math.sin(u)) * g - .25) + v * (c = -.5 * g * Math.cos(u))), l = io + (B * r + J * c), x = ro + (K * r + N * c + Q * g), m = co + (P * r + Z * c + G * g), p = ho + (H * r + Y * c), "n" === X && (x -= j, m -= q, p -= w, to += l -= T, oo += h -= b, n = Math.sin(to), a = Math.cos(to), .2 <= to ? (eo += m -= a * (p /= n), so += p, ao += x) : (s = n * (i = Math.sin(so)), e = n * (d = Math.cos(so)), s += p * d + l * a * i, e += -p * i + l * a * d, (so %= Yo) < 0 && "a" === $ && (so += Yo), M = ao + eo + a * so, M += x + m - l * so * n, f = so, (so = Math.atan2(s, e)) < 0 && "a" === $ && (so += Yo), Math.abs(f - so) > Ho && (so < f ? so += Yo : so -= Yo), eo = M - (ao += x) - a * so)),
        {
            ep: oo,
            inclp: to,
            nodep: so,
            argpp: eo,
            mp: ao
        }
    }

    function e(o)
    {
        var t = (o - 2451545) / 36525,
            s = -62e-7 * t * t * t + .093104 * t * t + 3164400184.812866 * t + 67310.54841;
        return (s = s * m / 240 % Yo) < 0 && (s += Yo), s
    }

    function So()
    {
        return (arguments.length <= 0 ? void 0 : arguments[0]) instanceof Date || 1 < arguments.length ? e(x.apply(void 0, arguments)) : e.apply(void 0, arguments)
    }

    function Io(o, t)
    {
        var s, e, a, n, d, i, r, c, h, m, p, l, x, g, M, f, u, z, v, y, b, q, w, T, j, E;
        o.t = t, o.error = 0;
        var F = o.mo + o.mdot * o.t,
            L = o.argpo + o.argpdot * o.t,
            A = o.nodeo + o.nodedot * o.t;
        c = L, y = F;
        var C = o.t * o.t;
        if (q = A + o.nodecf * C, f = 1 - o.cc1 * o.t, u = o.bstar * o.cc4 * o.t, z = o.t2cof * C, 1 !== o.isimp)
        {
            i = o.omgcof * o.t;
            var U = 1 + o.eta * Math.cos(F);
            y = F + (M = i + o.xmcof * (U * U * U - o.delmo)), c = L - M, l = (p = C * o.t) * o.t, f = f - o.d2 * C - o.d3 * p - o.d4 * l, u += o.bstar * o.cc5 * (Math.sin(y) - o.sinmao), z = z + o.t3cof * p + l * (o.t4cof + o.t * o.t5cof)
        }
        b = o.no;
        var D = o.ecco;
        if (v = o.inclo, "d" === o.method)
        {
            x = o.t;
            var R = function (o)
            {
                var t, s, e, a, n, d, i, r, c = o.irez,
                    h = o.d2201,
                    m = o.d2211,
                    p = o.d3210,
                    l = o.d3222,
                    x = o.d4410,
                    g = o.d4422,
                    M = o.d5220,
                    f = o.d5232,
                    u = o.d5421,
                    z = o.d5433,
                    v = o.dedt,
                    y = o.del1,
                    b = o.del2,
                    q = o.del3,
                    w = o.didt,
                    T = o.dmdt,
                    j = o.dnodt,
                    E = o.domdt,
                    F = o.argpo,
                    L = o.argpdot,
                    A = o.t,
                    C = o.tc,
                    U = o.gsto,
                    D = o.xfact,
                    R = o.xlamo,
                    S = o.no,
                    I = o.atime,
                    _ = o.em,
                    k = o.argpm,
                    O = o.inclm,
                    P = o.xli,
                    Z = o.mm,
                    G = o.xni,
                    H = o.nodem,
                    Y = o.nm,
                    B = .13130908,
                    J = 2.8843198,
                    K = .37448087,
                    N = 5.7686396,
                    Q = .95240898,
                    V = 1.8014998,
                    W = 1.050833,
                    X = 4.4108898,
                    $ = 0,
                    oo = 0,
                    to = (U + .0043752690880113 * C) % Yo;
                if (_ += v * A, O += w * A, k += E * A, H += j * A, Z += T * A, 0 !== c)
                {
                    (0 === I || A * I <= 0 || Math.abs(A) < Math.abs(I)) && (I = 0, G = S, P = R), t = 0 < A ? 720 : -720;
                    for (var so = 381; 381 === so;) d = 2 !== c ? (i = y * Math.sin(P - B) + b * Math.sin(2 * (P - J)) + q * Math.sin(3 * (P - K)), n = G + D, y * Math.cos(P - B) + 2 * b * Math.cos(2 * (P - J)) + 3 * q * Math.cos(3 * (P - K))) : (e = (r = F + L * I) + r, s = P + P, i = h * Math.sin(e + P - N) + m * Math.sin(P - N) + p * Math.sin(r + P - Q) + l * Math.sin(-r + P - Q) + x * Math.sin(e + s - V) + g * Math.sin(s - V) + M * Math.sin(r + P - W) + f * Math.sin(-r + P - W) + u * Math.sin(r + s - X) + z * Math.sin(-r + s - X), n = G + D, h * Math.cos(e + P - N) + m * Math.cos(P - N) + p * Math.cos(r + P - Q) + l * Math.cos(-r + P - Q) + M * Math.cos(r + P - W) + f * Math.cos(-r + P - W) + 2 * x * Math.cos(e + s - V) + g * Math.cos(s - V) + u * Math.cos(r + s - X) + z * Math.cos(-r + s - X)), d *= n, 381 == (so = 720 <= Math.abs(A - I) ? 381 : (oo = A - I, 0)) && (P += n * t + 259200 * i, G += i * t + 259200 * d, I += t);
                    a = P + n * oo + i * oo * oo * .5, Y = S + (Z = 1 !== c ? a - 2 * H + 2 * to : a - H - k + to, $ = (Y = G + i * oo + d * oo * oo * .5) - S)
                }
                return {
                    atime: I,
                    em: _,
                    argpm: k,
                    inclm: O,
                    xli: P,
                    mm: Z,
                    xni: G,
                    nodem: H,
                    dndt: $,
                    nm: Y
                }
            }(
            {
                irez: o.irez,
                d2201: o.d2201,
                d2211: o.d2211,
                d3210: o.d3210,
                d3222: o.d3222,
                d4410: o.d4410,
                d4422: o.d4422,
                d5220: o.d5220,
                d5232: o.d5232,
                d5421: o.d5421,
                d5433: o.d5433,
                dedt: o.dedt,
                del1: o.del1,
                del2: o.del2,
                del3: o.del3,
                didt: o.didt,
                dmdt: o.dmdt,
                dnodt: o.dnodt,
                domdt: o.domdt,
                argpo: o.argpo,
                argpdot: o.argpdot,
                t: o.t,
                tc: x,
                gsto: o.gsto,
                xfact: o.xfact,
                xlamo: o.xlamo,
                no: o.no,
                atime: o.atime,
                em: D,
                argpm: c,
                inclm: v,
                xli: o.xli,
                mm: y,
                xni: o.xni,
                nodem: q,
                nm: b
            });
            D = R.em, c = R.argpm, v = R.inclm, y = R.mm, q = R.nodem, b = R.nm
        }
        if (b <= 0) return [!(o.error = 2), !1];
        var S = Math.pow(Bo / b, Jo) * f * f;
        if (b = Bo / Math.pow(S, 1.5), 1 <= (D -= u) || D < -.001) return [!(o.error = 1), !1];
        D < 1e-6 && (D = 1e-6), T = (y += o.no * z) + c + q;
        var I = D;
        if (w = v, h = c %= Yo, E = q %= Yo, j = y = ((T %= Yo) - c - q) % Yo, n = Math.sin(v), a = Math.cos(v), "d" === o.method)
        {
            var _ = Ro(o,
            {
                inclo: o.inclo,
                init: "n",
                ep: I,
                inclp: w,
                nodep: E,
                argpp: h,
                mp: j,
                opsmode: o.operationmode
            });
            if (I = _.ep, E = _.nodep, h = _.argpp, j = _.mp, (w = _.inclp) < 0 && (w = -w, E += Ho, h -= Ho), I < 0 || 1 < I) return [!(o.error = 3), !1]
        }
        "d" === o.method && (n = Math.sin(w), a = Math.cos(w), o.aycof = -.5 * Do * n, 15e-13 < Math.abs(a + 1) ? o.xlcof = -.25 * Do * n * (3 + 5 * a) / (1 + a) : o.xlcof = -.25 * Do * n * (3 + 5 * a) / 15e-13);
        var k = I * Math.cos(h);
        M = 1 / (S * (1 - I * I));
        var O = I * Math.sin(h) + M * o.aycof,
            P = (j + h + E + M * o.xlcof * k - E) % Yo;
        r = P, g = 9999.9;
        for (var Z = 1; 1e-12 <= Math.abs(g) && Z <= 10;) e = Math.sin(r), g = (P - O * (s = Math.cos(r)) + k * e - r) / (g = 1 - s * k - e * O), .95 <= Math.abs(g) && (g = 0 < g ? .95 : -.95), r += g, Z += 1;
        var G = k * s + O * e,
            H = k * e - O * s,
            Y = k * k + O * O,
            B = S * (1 - Y);
        if (B < 0) return [!(o.error = 4), !1];
        var J = S * (1 - G),
            K = Math.sqrt(S) * H / J,
            N = Math.sqrt(B) / J,
            Q = Math.sqrt(1 - Y),
            V = S / J * (e - O - k * (M = H / (1 + Q))),
            W = S / J * (s - k + O * M);
        m = Math.atan2(V, W);
        var X = (W + W) * V,
            $ = 1 - 2 * V * V,
            oo = .5 * Co * (M = 1 / B),
            to = oo * M;
        "d" === o.method && (d = a * a, o.con41 = 3 * d - 1, o.x1mth2 = 1 - d, o.x7thm1 = 7 * d - 1);
        var so = J * (1 - 1.5 * to * Q * o.con41) + .5 * oo * o.x1mth2 * $;
        if (so < 1) return {
            position: !(o.error = 6),
            velocity: !1
        };
        m -= .25 * to * o.x7thm1 * X;
        var eo = E + 1.5 * to * a * X,
            ao = w + 1.5 * to * a * n * $,
            no = K - b * oo * o.x1mth2 * X / Bo,
            io = N + b * oo * (o.x1mth2 * $ + 1.5 * o.con41) / Bo,
            ro = Math.sin(m),
            co = Math.cos(m),
            ho = Math.sin(eo),
            mo = Math.cos(eo),
            po = Math.sin(ao),
            lo = Math.cos(ao),
            xo = -ho * lo,
            go = mo * lo,
            Mo = xo * ro + mo * co,
            fo = go * ro + ho * co,
            uo = po * ro;
        return {
            position:
            {
                x: so * Mo * Ao,
                y: so * fo * Ao,
                z: so * uo * Ao
            },
            velocity:
            {
                x: (no * Mo + io * (xo * co - mo * ro)) * zo,
                y: (no * fo + io * (go * co - ho * ro)) * zo,
                z: (no * uo + io * (po * co)) * zo
            }
        }
    }

    function g(o, t)
    {
        var s, e, a, n, d, i, r, c, h, m, p, l, x, g, M, f, u, z, v, y, b, q, w, T, j, E, F, L, A, C, U, D, R, S, I, _, k, O, P, Z, G, H, Y, B, J, K, N, Q, V, W, X, $, oo = t.opsmode,
            to = t.satn,
            so = t.epoch,
            eo = t.xbstar,
            ao = t.xecco,
            no = t.xargpo,
            io = t.xinclo,
            ro = t.xmo,
            co = t.xno,
            ho = t.xnodeo;
        o.isimp = 0, o.method = "n", o.aycof = 0, o.con41 = 0, o.cc1 = 0, o.cc4 = 0, o.cc5 = 0, o.d2 = 0, o.d3 = 0, o.d4 = 0, o.delmo = 0, o.eta = 0, o.argpdot = 0, o.omgcof = 0, o.sinmao = 0, o.t = 0, o.t2cof = 0, o.t3cof = 0, o.t4cof = 0, o.t5cof = 0, o.x1mth2 = 0, o.x7thm1 = 0, o.mdot = 0, o.nodedot = 0, o.xlcof = 0, o.xmcof = 0, o.nodecf = 0, o.irez = 0, o.d2201 = 0, o.d2211 = 0, o.d3210 = 0, o.d3222 = 0, o.d4410 = 0, o.d4422 = 0, o.d5220 = 0, o.d5232 = 0, o.d5421 = 0, o.d5433 = 0, o.dedt = 0, o.del1 = 0, o.del2 = 0, o.del3 = 0, o.didt = 0, o.dmdt = 0, o.dnodt = 0, o.domdt = 0, o.e3 = 0, o.ee2 = 0, o.peo = 0, o.pgho = 0, o.pho = 0, o.pinco = 0, o.plo = 0, o.se2 = 0, o.se3 = 0, o.sgh2 = 0, o.sgh3 = 0, o.sgh4 = 0, o.sh2 = 0, o.sh3 = 0, o.si2 = 0, o.si3 = 0, o.sl2 = 0, o.sl3 = 0, o.sl4 = 0, o.gsto = 0, o.xfact = 0, o.xgh2 = 0, o.xgh3 = 0, o.xgh4 = 0, o.xh2 = 0, o.xh3 = 0, o.xi2 = 0, o.xi3 = 0, o.xl2 = 0, o.xl3 = 0, o.xl4 = 0, o.xlamo = 0, o.zmol = 0, o.zmos = 0, o.atime = 0, o.xli = 0, o.xni = 0, o.bstar = eo, o.ecco = ao, o.argpo = no, o.inclo = io, o.mo = ro, o.no = co, o.nodeo = ho, o.operationmode = oo;
        var mo = 78 / Ao + 1,
            po = 42 / Ao,
            lo = po * po * po * po;
        o.init = "y", o.t = 0;
        var xo = function (o)
            {
                var t = o.ecco,
                    s = o.epoch,
                    e = o.inclo,
                    a = o.opsmode,
                    n = o.no,
                    d = t * t,
                    i = 1 - d,
                    r = Math.sqrt(i),
                    c = Math.cos(e),
                    h = c * c,
                    m = Math.pow(Bo / n, Jo),
                    p = .75 * Co * (3 * h - 1) / (r * i),
                    l = p / (m * m),
                    x = m * (1 - l * l - l * (1 / 3 + 134 * l * l / 81));
                n /= 1 + (l = p / (x * x));
                var g, M = Math.pow(Bo / n, Jo),
                    f = Math.sin(e),
                    u = M * i,
                    z = 1 - 5 * h,
                    v = -z - h - h,
                    y = 1 / M,
                    b = u * u,
                    q = M * (1 - t);
                if ("a" === a)
                {
                    var w = s - 7305,
                        T = Math.floor(w + 1e-8),
                        j = .017202791694070362;
                    (g = (1.7321343856509375 + j * T + (j + Yo) * (w - T) + w * w * 5075514194322695e-30) % Yo) < 0 && (g += Yo)
                }
                else g = So(s + 2433281.5);
                return {
                    no: n,
                    method: "n",
                    ainv: y,
                    ao: M,
                    con41: v,
                    con42: z,
                    cosio: c,
                    cosio2: h,
                    eccsq: d,
                    omeosq: i,
                    posq: b,
                    rp: q,
                    rteosq: r,
                    sinio: f,
                    gsto: g
                }
            }(
            {
                satn: to,
                ecco: o.ecco,
                epoch: so,
                inclo: o.inclo,
                no: o.no,
                method: o.method,
                opsmode: o.operationmode
            }),
            go = xo.ao,
            Mo = xo.con42,
            fo = xo.cosio,
            uo = xo.cosio2,
            zo = xo.eccsq,
            vo = xo.omeosq,
            yo = xo.posq,
            bo = xo.rp,
            qo = xo.rteosq,
            wo = xo.sinio;
        if (o.no = xo.no, o.con41 = xo.con41, o.gsto = xo.gsto, (o.error = 0) <= vo || 0 <= o.no)
        {
            if (o.isimp = 0, bo < 220 / Ao + 1 && (o.isimp = 1), T = mo, z = lo, (M = (bo - 1) * Ao) < 156)
            {
                T = M - 78, M < 98 && (T = 20);
                var To = (120 - T) / Ao;
                z = To * To * To * To, T = T / Ao + 1
            }
            f = 1 / yo, H = 1 / (go - T), o.eta = go * o.ecco * H, l = o.eta * o.eta, p = o.ecco * o.eta, u = Math.abs(1 - l), n = (r = (i = z * Math.pow(H, 4)) / Math.pow(u, 3.5)) * o.no * (go * (1 + 1.5 * l + p * (4 + l)) + .375 * Co * H / u * o.con41 * (8 + 3 * l * (8 + l))), o.cc1 = o.bstar * n, d = 0, 1e-4 < o.ecco && (d = -2 * i * H * Do * o.no * wo / o.ecco), o.x1mth2 = 1 - uo, o.cc4 = 2 * o.no * r * go * vo * (o.eta * (2 + .5 * l) + o.ecco * (.5 + 2 * l) - Co * H / (go * u) * (-3 * o.con41 * (1 - 2 * p + l * (1.5 - .5 * p)) + .75 * o.x1mth2 * (2 * l - p * (1 + l)) * Math.cos(2 * o.argpo))), o.cc5 = 2 * r * go * vo * (1 + 2.75 * (l + p) + p * l), c = uo * uo, Z = .5 * (P = 1.5 * Co * f * o.no) * Co * f, G = -.46875 * Uo * f * f * o.no, o.mdot = o.no + .5 * P * qo * o.con41 + .0625 * Z * qo * (13 - 78 * uo + 137 * c), o.argpdot = -.5 * P * Mo + .0625 * Z * (7 - 114 * uo + 395 * c) + G * (3 - 36 * uo + 49 * c), B = -P * fo, o.nodedot = B + (.5 * Z * (4 - 19 * uo) + 2 * G * (3 - 7 * uo)) * fo, Y = o.argpdot + o.nodedot, o.omgcof = o.bstar * d * Math.cos(o.argpo), o.xmcof = 0, 1e-4 < o.ecco && (o.xmcof = -Jo * i * o.bstar / p), o.nodecf = 3.5 * vo * B * o.cc1, o.t2cof = 1.5 * o.cc1, 15e-13 < Math.abs(fo + 1) ? o.xlcof = -.25 * Do * wo * (3 + 5 * fo) / (1 + fo) : o.xlcof = -.25 * Do * wo * (3 + 5 * fo) / 15e-13, o.aycof = -.5 * Do * wo;
            var jo = 1 + o.eta * Math.cos(o.mo);
            if (o.delmo = jo * jo * jo, o.sinmao = Math.sin(o.mo), o.x7thm1 = 7 * uo - 1, 225 <= 2 * Ho / o.no)
            {
                o.method = "d", o.isimp = 1, 0, x = o.inclo;
                var Eo = function (o)
                {
                    var t, s, e, a, n, d, i, r, c, h, m, p, l, x, g, M, f, u, z, v, y, b, q, w, T, j, E, F, L, A, C, U, D, R, S, I, _, k, O, P, Z, G, H, Y, B, J, K, N, Q, V, W, X, $, oo, to, so, eo, ao, no, io, ro, co, ho, mo = o.epoch,
                        po = o.ep,
                        lo = o.argpp,
                        xo = o.tc,
                        go = o.inclp,
                        Mo = o.nodep,
                        fo = o.np,
                        uo = po,
                        zo = Math.sin(Mo),
                        vo = Math.cos(Mo),
                        yo = Math.sin(lo),
                        bo = Math.cos(lo),
                        qo = Math.sin(go),
                        wo = Math.cos(go),
                        To = uo * uo,
                        jo = 1 - To,
                        Eo = Math.sqrt(jo),
                        Fo = mo + 18261.5 + xo / 1440,
                        Lo = (4.523602 - .00092422029 * Fo) % Yo,
                        Ao = Math.sin(Lo),
                        Co = Math.cos(Lo),
                        Uo = .91375164 - .03568096 * Co,
                        Do = Math.sqrt(1 - Uo * Uo),
                        Ro = .089683511 * Ao / Do,
                        So = Math.sqrt(1 - Ro * Ro),
                        Io = 5.8351514 + .001944368 * Fo,
                        _o = .39785416 * Ao / Do,
                        ko = So * Co + .91744867 * Ro * Ao;
                    _o = Math.atan2(_o, ko), _o += Io - Lo;
                    var Oo = Math.cos(_o),
                        Po = Math.sin(_o);
                    v = .1945905, y = -.98088458, w = .91744867, T = .39785416, b = vo, q = zo, m = 29864797e-13;
                    for (var Zo = 1 / fo, Go = 0; Go < 2;) to = -6 * (t = v * b + y * w * q) * (n = -qo * (i = -v * q + y * w * b) + wo * (r = y * T)) + To * (-24 * (p = t * bo + (s = wo * i + qo * r) * yo) * (u = n * bo) - 6 * (x = -t * yo + s * bo) * (M = n * yo)), so = -6 * (t * (d = -qo * (c = y * q + v * w * b) + wo * (h = v * T)) + (e = -y * b + v * w * q) * n) + To * (-24 * ((l = e * bo + (a = wo * c + qo * h) * yo) * u + p * (z = d * bo)) + -6 * (x * (f = d * yo) + (g = -e * yo + a * bo) * M)), eo = -6 * e * d + To * (-24 * l * z - 6 * g * f), ao = 6 * s * n + To * (24 * p * M - 6 * x * u), no = 6 * (a * n + s * d) + To * (24 * (l * M + p * f) - 6 * (g * u + x * z)), io = 6 * a * d + To * (24 * l * f - 6 * g * z), X = (X = 3 * (t * t + s * s) + (ro = 12 * p * p - 3 * x * x) * To) + X + jo * ro, $ = ($ = 6 * (t * e + s * a) + (co = 24 * p * l - 6 * x * g) * To) + $ + jo * co, oo = (oo = 3 * (e * e + a * a) + (ho = 12 * l * l - 3 * g * g) * To) + oo + jo * ho, J = -.5 * (K = m * Zo) / Eo, B = -15 * uo * (N = K * Eo), Q = p * x + l * g, V = l * x + p * g, W = l * g - p * x, 1 === (Go += 1) && (j = B, E = J, F = K, L = N, A = Q, C = V, U = W, D = X, R = $, S = oo, I = to, _ = so, k = eo, O = ao, P = no, Z = io, G = ro, H = co, Y = ho, v = Oo, y = Po, w = Uo, T = Do, b = So * vo + Ro * zo, q = zo * So - vo * Ro, m = 4.7968065e-7);
                    return {
                        snodm: zo,
                        cnodm: vo,
                        sinim: qo,
                        cosim: wo,
                        sinomm: yo,
                        cosomm: bo,
                        day: Fo,
                        e3: 2 * B * W,
                        ee2: 2 * B * V,
                        em: uo,
                        emsq: To,
                        gam: Io,
                        peo: 0,
                        pgho: 0,
                        pho: 0,
                        pinco: 0,
                        plo: 0,
                        rtemsq: Eo,
                        se2: 2 * j * C,
                        se3: 2 * j * U,
                        sgh2: 2 * L * H,
                        sgh3: 2 * L * (Y - G),
                        sgh4: -18 * L * .01675,
                        sh2: -2 * E * P,
                        sh3: -2 * E * (Z - O),
                        si2: 2 * E * _,
                        si3: 2 * E * (k - I),
                        sl2: -2 * F * R,
                        sl3: -2 * F * (S - D),
                        sl4: -2 * F * (-21 - 9 * To) * .01675,
                        s1: B,
                        s2: J,
                        s3: K,
                        s4: N,
                        s5: Q,
                        s6: V,
                        s7: W,
                        ss1: j,
                        ss2: E,
                        ss3: F,
                        ss4: L,
                        ss5: A,
                        ss6: C,
                        ss7: U,
                        sz1: D,
                        sz2: R,
                        sz3: S,
                        sz11: I,
                        sz12: _,
                        sz13: k,
                        sz21: O,
                        sz22: P,
                        sz23: Z,
                        sz31: G,
                        sz32: H,
                        sz33: Y,
                        xgh2: 2 * N * co,
                        xgh3: 2 * N * (ho - ro),
                        xgh4: -18 * N * .0549,
                        xh2: -2 * J * no,
                        xh3: -2 * J * (io - ao),
                        xi2: 2 * J * so,
                        xi3: 2 * J * (eo - to),
                        xl2: -2 * K * $,
                        xl3: -2 * K * (oo - X),
                        xl4: -2 * K * (-21 - 9 * To) * .0549,
                        nm: fo,
                        z1: X,
                        z2: $,
                        z3: oo,
                        z11: to,
                        z12: so,
                        z13: eo,
                        z21: ao,
                        z22: no,
                        z23: io,
                        z31: ro,
                        z32: co,
                        z33: ho,
                        zmol: (.2299715 * Fo - Io + 4.7199672) % Yo,
                        zmos: (6.2565837 + .017201977 * Fo) % Yo
                    }
                }(
                {
                    epoch: so,
                    ep: o.ecco,
                    argpp: o.argpo,
                    tc: 0,
                    inclp: o.inclo,
                    nodep: o.nodeo,
                    np: o.no,
                    e3: o.e3,
                    ee2: o.ee2,
                    peo: o.peo,
                    pgho: o.pgho,
                    pho: o.pho,
                    pinco: o.pinco,
                    plo: o.plo,
                    se2: o.se2,
                    se3: o.se3,
                    sgh2: o.sgh2,
                    sgh3: o.sgh3,
                    sgh4: o.sgh4,
                    sh2: o.sh2,
                    sh3: o.sh3,
                    si2: o.si2,
                    si3: o.si3,
                    sl2: o.sl2,
                    sl3: o.sl3,
                    sl4: o.sl4,
                    xgh2: o.xgh2,
                    xgh3: o.xgh3,
                    xgh4: o.xgh4,
                    xh2: o.xh2,
                    xh3: o.xh3,
                    xi2: o.xi2,
                    xi3: o.xi3,
                    xl2: o.xl2,
                    xl3: o.xl3,
                    xl4: o.xl4,
                    zmol: o.zmol,
                    zmos: o.zmos
                });
                o.e3 = Eo.e3, o.ee2 = Eo.ee2, o.peo = Eo.peo, o.pgho = Eo.pgho, o.pho = Eo.pho, o.pinco = Eo.pinco, o.plo = Eo.plo, o.se2 = Eo.se2, o.se3 = Eo.se3, o.sgh2 = Eo.sgh2, o.sgh3 = Eo.sgh3, o.sgh4 = Eo.sgh4, o.sh2 = Eo.sh2, o.sh3 = Eo.sh3, o.si2 = Eo.si2, o.si3 = Eo.si3, o.sl2 = Eo.sl2, o.sl3 = Eo.sl3, o.sl4 = Eo.sl4, e = Eo.sinim, s = Eo.cosim, h = Eo.em, m = Eo.emsq, v = Eo.s1, y = Eo.s2, b = Eo.s3, q = Eo.s4, w = Eo.s5, j = Eo.ss1, E = Eo.ss2, F = Eo.ss3, L = Eo.ss4, A = Eo.ss5, C = Eo.sz1, U = Eo.sz3, D = Eo.sz11, R = Eo.sz13, S = Eo.sz21, I = Eo.sz23, _ = Eo.sz31, k = Eo.sz33, o.xgh2 = Eo.xgh2, o.xgh3 = Eo.xgh3, o.xgh4 = Eo.xgh4, o.xh2 = Eo.xh2, o.xh3 = Eo.xh3, o.xi2 = Eo.xi2, o.xi3 = Eo.xi3, o.xl2 = Eo.xl2, o.xl3 = Eo.xl3, o.xl4 = Eo.xl4, o.zmol = Eo.zmol, o.zmos = Eo.zmos, g = Eo.nm, J = Eo.z1, K = Eo.z3, N = Eo.z11, Q = Eo.z13, V = Eo.z21, W = Eo.z23, X = Eo.z31, $ = Eo.z33;
                var Fo = Ro(o,
                {
                    inclo: x,
                    init: o.init,
                    ep: o.ecco,
                    inclp: o.inclo,
                    nodep: o.nodeo,
                    argpp: o.argpo,
                    mp: o.mo,
                    opsmode: o.operationmode
                });
                o.ecco = Fo.ep, o.inclo = Fo.inclp, o.nodeo = Fo.nodep, o.argpo = Fo.argpp, o.mo = Fo.mp, 0;
                var Lo = function (o)
                {
                    var t, s, e, a, n, d, i, r, c, h, m, p, l, x, g, M, f = o.cosim,
                        u = o.argpo,
                        z = o.s1,
                        v = o.s2,
                        y = o.s3,
                        b = o.s4,
                        q = o.s5,
                        w = o.sinim,
                        T = o.ss1,
                        j = o.ss2,
                        E = o.ss3,
                        F = o.ss4,
                        L = o.ss5,
                        A = o.sz1,
                        C = o.sz3,
                        U = o.sz11,
                        D = o.sz13,
                        R = o.sz21,
                        S = o.sz23,
                        I = o.sz31,
                        _ = o.sz33,
                        k = o.t,
                        O = o.tc,
                        P = o.gsto,
                        Z = o.mo,
                        G = o.mdot,
                        H = o.no,
                        Y = o.nodeo,
                        B = o.nodedot,
                        J = o.xpidot,
                        K = o.z1,
                        N = o.z3,
                        Q = o.z11,
                        V = o.z13,
                        W = o.z21,
                        X = o.z23,
                        $ = o.z31,
                        oo = o.z33,
                        to = o.ecco,
                        so = o.eccsq,
                        eo = o.emsq,
                        ao = o.em,
                        no = o.argpm,
                        io = o.inclm,
                        ro = o.mm,
                        co = o.nm,
                        ho = o.nodem,
                        mo = o.irez,
                        po = o.atime,
                        lo = o.d2201,
                        xo = o.d2211,
                        go = o.d3210,
                        Mo = o.d3222,
                        fo = o.d4410,
                        uo = o.d4422,
                        zo = o.d5220,
                        vo = o.d5232,
                        yo = o.d5421,
                        bo = o.d5433,
                        qo = o.dedt,
                        wo = o.didt,
                        To = o.dmdt,
                        jo = o.dnodt,
                        Eo = o.domdt,
                        Fo = o.del1,
                        Lo = o.del2,
                        Ao = o.del3,
                        Co = o.xfact,
                        Uo = o.xlamo,
                        Do = o.xli,
                        Ro = o.xni,
                        So = .0043752690880113,
                        Io = .00015835218,
                        _o = 119459e-10;
                    mo = 0, co < .0052359877 && .0034906585 < co && (mo = 1), .00826 <= co && co <= .00924 && .5 <= ao && (mo = 2);
                    var ko = -_o * j * (R + S);
                    (io < .052359877 || Ho - .052359877 < io) && (ko = 0), 0 !== w && (ko /= w);
                    var Oo = -Io * v * (W + X);
                    (io < .052359877 || Ho - .052359877 < io) && (Oo = 0), Eo = F * _o * (I + _ - 6) - f * ko + b * Io * ($ + oo - 6), jo = ko, 0 !== w && (Eo -= f / w * Oo, jo += Oo / w);
                    var Po = (P + O * So) % Yo;
                    if (ao += (qo = T * _o * L + z * Io * q) * k, io += (wo = j * _o * (U + D) + v * Io * (Q + V)) * k, no += Eo * k, ho += jo * k, ro += (To = -_o * E * (A + C - 14 - 6 * eo) - Io * y * (K + N - 14 - 6 * eo)) * k, 0 !== mo)
                    {
                        if (x = Math.pow(co / Bo, Jo), 2 === mo)
                        {
                            var Zo = ao,
                                Go = eo;
                            M = (ao = to) * (eo = so), r = ao <= .65 ? (e = 3.616 - 13.247 * ao + 16.29 * eo, a = 117.39 * ao - 19.302 - 228.419 * eo + 156.591 * M, n = 109.7927 * ao - 18.9068 - 214.6334 * eo + 146.5816 * M, d = 242.694 * ao - 41.122 - 471.094 * eo + 313.953 * M, i = 841.88 * ao - 146.407 - 1629.014 * eo + 1083.435 * M, 3017.977 * ao - 532.114 - 5740.032 * eo + 3708.276 * M) : (e = 331.819 * ao - 72.099 - 508.738 * eo + 266.724 * M, a = 1582.851 * ao - 346.844 - 2415.925 * eo + 1246.113 * M, n = 1554.908 * ao - 342.585 - 2366.899 * eo + 1215.972 * M, d = 4758.686 * ao - 1052.797 - 7193.992 * eo + 3651.957 * M, i = 16178.11 * ao - 3581.69 - 24462.77 * eo + 12422.52 * M, .715 < ao ? 29936.92 * ao - 5149.66 - 54087.36 * eo + 31324.56 * M : 1464.74 - 4664.75 * ao + 3763.64 * eo), lo = (p = 17891679e-13 * (l = co * co * 3 * (x * x))) * (t = .75 * (1 + 2 * f + (g = f * f))) * (-.306 - .44 * (ao - .64)), xo = p * (1.5 * (m = w * w)) * e, go = (p = 3.7393792e-7 * (l *= x)) * (1.875 * w * (1 - 2 * f - 3 * g)) * a, Mo = p * (-1.875 * w * (1 + 2 * f - 3 * g)) * n, fo = (p = 2 * (l *= x) * 7.3636953e-9) * (35 * m * t) * d, uo = p * (39.375 * m * m) * i, zo = (p = 1.1428639e-7 * (l *= x)) * (9.84375 * w * (m * (1 - 2 * f - 5 * g) + .33333333 * (4 * f - 2 + 6 * g))) * r, vo = p * (w * (4.92187512 * m * (-2 - 4 * f + 10 * g) + 6.56250012 * (1 + 2 * f - 3 * g))) * (ao < .7 ? (h = 4988.61 * ao - 919.2277 - 9064.77 * eo + 5542.21 * M, c = 4568.6173 * ao - 822.71072 - 8491.4146 * eo + 5337.524 * M, 4690.25 * ao - 853.666 - 8624.77 * eo + 5341.4 * M) : (h = 161616.52 * ao - 37995.78 - 229838.2 * eo + 109377.94 * M, c = 218913.95 * ao - 51752.104 - 309468.16 * eo + 146349.42 * M, 170470.89 * ao - 40023.88 - 242699.48 * eo + 115605.82 * M)), yo = (p = 2 * l * 2.1765803e-9) * (29.53125 * w * (2 - 8 * f + g * (8 * f - 12 + 10 * g))) * c, bo = p * (29.53125 * w * (-2 - 8 * f + g * (12 + 8 * f - 10 * g))) * h, Uo = (Z + Y + Y - (Po + Po)) % Yo, Co = G + To + 2 * (B + jo - So) - H, ao = Zo, eo = Go
                        }
                        1 === mo && (Lo = 2 * (Fo = 3 * co * co * x * x) * (t = .75 * (s = 1 + f) * (1 + f)) * (1 + eo * (.8125 * eo - 2.5)) * 17891679e-13, Ao = 3 * Fo * (s *= 1.875 * s * s) * (1 + eo * (6.60937 * eo - 6)) * 2.2123015e-7 * x, Fo = Fo * (.9375 * w * w * (1 + 3 * f) - .75 * (1 + f)) * (a = 1 + 2 * eo) * 21460748e-13 * x, Uo = (Z + Y + u - Po) % Yo, Co = G + J + To + Eo + jo - (H + So)), Do = Uo, co = (Ro = H) + (po = 0)
                    }
                    return {
                        em: ao,
                        argpm: no,
                        inclm: io,
                        mm: ro,
                        nm: co,
                        nodem: ho,
                        irez: mo,
                        atime: po,
                        d2201: lo,
                        d2211: xo,
                        d3210: go,
                        d3222: Mo,
                        d4410: fo,
                        d4422: uo,
                        d5220: zo,
                        d5232: vo,
                        d5421: yo,
                        d5433: bo,
                        dedt: qo,
                        didt: wo,
                        dmdt: To,
                        dndt: 0,
                        dnodt: jo,
                        domdt: Eo,
                        del1: Fo,
                        del2: Lo,
                        del3: Ao,
                        xfact: Co,
                        xlamo: Uo,
                        xli: Do,
                        xni: Ro
                    }
                }(
                {
                    cosim: s,
                    emsq: m,
                    argpo: o.argpo,
                    s1: v,
                    s2: y,
                    s3: b,
                    s4: q,
                    s5: w,
                    sinim: e,
                    ss1: j,
                    ss2: E,
                    ss3: F,
                    ss4: L,
                    ss5: A,
                    sz1: C,
                    sz3: U,
                    sz11: D,
                    sz13: R,
                    sz21: S,
                    sz23: I,
                    sz31: _,
                    sz33: k,
                    t: o.t,
                    tc: 0,
                    gsto: o.gsto,
                    mo: o.mo,
                    mdot: o.mdot,
                    no: o.no,
                    nodeo: o.nodeo,
                    nodedot: o.nodedot,
                    xpidot: Y,
                    z1: J,
                    z3: K,
                    z11: N,
                    z13: Q,
                    z21: V,
                    z23: W,
                    z31: X,
                    z33: $,
                    ecco: o.ecco,
                    eccsq: zo,
                    em: h,
                    argpm: 0,
                    inclm: x,
                    mm: 0,
                    nm: g,
                    nodem: 0,
                    irez: o.irez,
                    atime: o.atime,
                    d2201: o.d2201,
                    d2211: o.d2211,
                    d3210: o.d3210,
                    d3222: o.d3222,
                    d4410: o.d4410,
                    d4422: o.d4422,
                    d5220: o.d5220,
                    d5232: o.d5232,
                    d5421: o.d5421,
                    d5433: o.d5433,
                    dedt: o.dedt,
                    didt: o.didt,
                    dmdt: o.dmdt,
                    dnodt: o.dnodt,
                    domdt: o.domdt,
                    del1: o.del1,
                    del2: o.del2,
                    del3: o.del3,
                    xfact: o.xfact,
                    xlamo: o.xlamo,
                    xli: o.xli,
                    xni: o.xni
                });
                o.irez = Lo.irez, o.atime = Lo.atime, o.d2201 = Lo.d2201, o.d2211 = Lo.d2211, o.d3210 = Lo.d3210, o.d3222 = Lo.d3222, o.d4410 = Lo.d4410, o.d4422 = Lo.d4422, o.d5220 = Lo.d5220, o.d5232 = Lo.d5232, o.d5421 = Lo.d5421, o.d5433 = Lo.d5433, o.dedt = Lo.dedt, o.didt = Lo.didt, o.dmdt = Lo.dmdt, o.dnodt = Lo.dnodt, o.domdt = Lo.domdt, o.del1 = Lo.del1, o.del2 = Lo.del2, o.del3 = Lo.del3, o.xfact = Lo.xfact, o.xlamo = Lo.xlamo, o.xli = Lo.xli, o.xni = Lo.xni
            }
            1 !== o.isimp && (a = o.cc1 * o.cc1, o.d2 = 4 * go * H * a, O = o.d2 * H * o.cc1 / 3, o.d3 = (17 * go + T) * O, o.d4 = .5 * O * go * H * (221 * go + 31 * T) * o.cc1, o.t3cof = o.d2 + 2 * a, o.t4cof = .25 * (3 * o.d3 + o.cc1 * (12 * o.d2 + 10 * a)), o.t5cof = .2 * (3 * o.d4 + 12 * o.cc1 * o.d3 + 6 * o.d2 * o.d2 + 15 * a * (2 * o.d2 + a)))
        }
        Io(o, 0), o.init = "n"
    }

    function n(o)
    {
        return function (o)
        {
            if (Array.isArray(o))
            {
                for (var t = 0, s = new Array(o.length); t < o.length; t++) s[t] = o[t];
                return s
            }
        }(o) || function (o)
        {
            if (Symbol.iterator in Object(o) || "[object Arguments]" === Object.prototype.toString.call(o)) return Array.from(o)
        }(o) || function ()
        {
            throw new TypeError("Invalid attempt to spread non-iterable instance")
        }()
    }

    function a(o)
    {
        return o * t
    }

    function d(o)
    {
        return o * m
    }

    function f(o)
    {
        var t = o.longitude,
            s = o.latitude,
            e = o.height,
            a = 6378.137,
            n = .0033528106718309306,
            d = 2 * n - n * n,
            i = a / Math.sqrt(1 - d * (Math.sin(s) * Math.sin(s)));
        return {
            x: (i + e) * Math.cos(s) * Math.cos(t),
            y: (i + e) * Math.cos(s) * Math.sin(t),
            z: (i * (1 - d) + e) * Math.sin(s)
        }
    }
    return {
        constants: Object.freeze(
        {
            __proto__: null,
            pi: Ho,
            twoPi: Yo,
            deg2rad: m,
            rad2deg: t,
            minutesPerDay: 1440,
            mu: o,
            earthRadius: Ao,
            xke: Bo,
            vkmpersec: zo,
            tumin: p,
            j2: Co,
            j3: s,
            j4: Uo,
            j3oj2: Do,
            x2o3: Jo
        }),
        propagate: function ()
        {
            for (var o = arguments.length, t = new Array(o), s = 0; s < o; s++) t[s] = arguments[s];
            var e = t[0],
                a = Array.prototype.slice.call(t, 1);
            return Io(e, 1440 * (x.apply(void 0, n(a)) - e.jdsatepoch))
        },
        sgp4: Io,
        twoline2satrec: function (o, t)
        {
            var s = 1440 / (2 * Ho),
                e = 0,
                a = {
                    error: 0
                };
            a.satnum = o.substring(2, 7), a.epochyr = parseInt(o.substring(18, 20), 10), a.epochdays = parseFloat(o.substring(20, 32)), a.ndot = parseFloat(o.substring(33, 43)), a.nddot = parseFloat(".".concat(parseInt(o.substring(44, 50), 10), "E").concat(o.substring(50, 52))), a.bstar = parseFloat("".concat(o.substring(53, 54), ".").concat(parseInt(o.substring(54, 59), 10), "E").concat(o.substring(59, 61))), a.inclo = parseFloat(t.substring(8, 16)), a.nodeo = parseFloat(t.substring(17, 25)), a.ecco = parseFloat(".".concat(t.substring(26, 33))), a.argpo = parseFloat(t.substring(34, 42)), a.mo = parseFloat(t.substring(43, 51)), a.no = parseFloat(t.substring(52, 63)), a.no /= s, a.a = Math.pow(a.no * p, -2 / 3), a.ndot /= 1440 * s, a.nddot /= 1440 * s * 1440, a.inclo *= m, a.nodeo *= m, a.argpo *= m, a.mo *= m, a.alta = a.a * (1 + a.ecco) - 1, a.altp = a.a * (1 - a.ecco) - 1;
            var n = l(e = a.epochyr < 57 ? a.epochyr + 2e3 : a.epochyr + 1900, a.epochdays),
                d = n.mon,
                i = n.day,
                r = n.hr,
                c = n.minute,
                h = n.sec;
            return a.jdsatepoch = x(e, d, i, r, c, h), g(a,
            {
                opsmode: "i",
                satn: a.satnum,
                epoch: a.jdsatepoch - 2433281.5,
                xbstar: a.bstar,
                xecco: a.ecco,
                xargpo: a.argpo,
                xinclo: a.inclo,
                xmo: a.mo,
                xno: a.no,
                xnodeo: a.nodeo
            }), a
        },
        gstime: So,
        jday: x,
        invjday: function (o, t)
        {
            var s = o - 2415019.5,
                e = s / 365.25,
                a = 1900 + Math.floor(e),
                n = Math.floor(.25 * (a - 1901)),
                d = s - (365 * (a - 1900) + n) + 1e-11;
            d < 1 && (d = s - (365 * ((a -= 1) - 1900) + (n = Math.floor(.25 * (a - 1901)))));
            var i = l(a, d),
                r = i.mon,
                c = i.day,
                h = i.hr,
                m = i.minute,
                p = i.sec - 864e-9;
            return t ? [a, r, c, h, m, Math.floor(p)] : new Date(Date.UTC(a, r - 1, c, h, m, Math.floor(p)))
        },
        dopplerFactor: function (o, t, s)
        {
            var e = Math.sqrt(Math.pow(t.x - o.x, 2) + Math.pow(t.y - o.y, 2) + Math.pow(t.z - o.z, 2)),
                a = t.x + s.x,
                n = t.y + s.y,
                d = t.z + s.z,
                i = Math.sqrt(Math.pow(a - o.x, 2) + Math.pow(n - o.y, 2) + Math.pow(d - o.z, 2)) - e;
            return 1 + (i *= 0 <= i ? 1 : -1) / 299792.458
        },
        radiansToDegrees: a,
        degreesToRadians: d,
        degreesLat: function (o)
        {
            if (o < -Ho / 2 || Ho / 2 < o) throw new RangeError("Latitude radians must be in range [-pi/2; pi/2].");
            return a(o)
        },
        degreesLong: function (o)
        {
            if (o < -Ho || Ho < o) throw new RangeError("Longitude radians must be in range [-pi; pi].");
            return a(o)
        },
        radiansLat: function (o)
        {
            if (o < -90 || 90 < o) throw new RangeError("Latitude degrees must be in range [-90; 90].");
            return d(o)
        },
        radiansLong: function (o)
        {
            if (o < -180 || 180 < o) throw new RangeError("Longitude degrees must be in range [-180; 180].");
            return d(o)
        },
        geodeticToEcf: f,
        eciToGeodetic: function (o, t)
        {
            for (var s = 6378.137, e = Math.sqrt(o.x * o.x + o.y * o.y), a = .0033528106718309306, n = 2 * a - a * a, d = Math.atan2(o.y, o.x) - t; d < -Ho;) d += Yo;
            for (; Ho < d;) d -= Yo;
            for (var i, r = 0, c = Math.atan2(o.z, Math.sqrt(o.x * o.x + o.y * o.y)); r < 20;) i = 1 / Math.sqrt(1 - n * (Math.sin(c) * Math.sin(c))), c = Math.atan2(o.z + s * i * n * Math.sin(c), e), r += 1;
            return {
                longitude: d,
                latitude: c,
                height: e / Math.cos(c) - s * i
            }
        },
        eciToEcf: function (o, t)
        {
            return {
                x: o.x * Math.cos(t) + o.y * Math.sin(t),
                y: o.x * -Math.sin(t) + o.y * Math.cos(t),
                z: o.z
            }
        },
        ecfToEci: function (o, t)
        {
            return {
                x: o.x * Math.cos(t) - o.y * Math.sin(t),
                y: o.x * Math.sin(t) + o.y * Math.cos(t),
                z: o.z
            }
        },
        ecfToLookAngles: function (o, t)
        {
            var s, e, a, n, d, i, r, c, h, m, p, l, x, g, M = (e = t, a = (s = o).longitude, n = s.latitude, d = f(s), i = e.x - d.x, r = e.y - d.y, c = e.z - d.z,
            {
                topS: Math.sin(n) * Math.cos(a) * i + Math.sin(n) * Math.sin(a) * r - Math.cos(n) * c,
                topE: -Math.sin(a) * i + Math.cos(a) * r,
                topZ: Math.cos(n) * Math.cos(a) * i + Math.cos(n) * Math.sin(a) * r + Math.sin(n) * c
            });
            return m = (h = M).topS, p = h.topE, l = h.topZ, x = Math.sqrt(m * m + p * p + l * l), g = Math.asin(l / x),
            {
                azimuth: Math.atan2(-p, m) + Ho,
                elevation: g,
                rangeSat: x
            }
        }
    }
});