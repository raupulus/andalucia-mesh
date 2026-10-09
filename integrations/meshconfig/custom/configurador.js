typeof window < "u" && (window.global = window.global || window, window.process = window.process || {
	env: { NODE_ENV: "production" },
	version: "",
	versions: {},
	platform: "browser",
	cwd: function() {
		return "";
	}
}, window.Buffer || (window.Buffer = {
	isBuffer: function(e) {
		return !!(e && (e._isBuffer || e.constructor && e.constructor.isBuffer && e.constructor.isBuffer(e)));
	},
	from: function(e) {
		return new Uint8Array(e);
	},
	alloc: function(e) {
		return new Uint8Array(e);
	}
}, globalThis.Buffer = window.Buffer));
//#region node_modules/@bufbuild/protobuf/dist/esm/is-message.js
function e(e, t) {
	return typeof e == "object" && e && "$typeName" in e && typeof e.$typeName == "string" ? t === void 0 || t.typeName === e.$typeName : !1;
}
//#endregion
//#region node_modules/@bufbuild/protobuf/dist/esm/descriptors.js
var t;
(function(e) {
	e[e.DOUBLE = 1] = "DOUBLE", e[e.FLOAT = 2] = "FLOAT", e[e.INT64 = 3] = "INT64", e[e.UINT64 = 4] = "UINT64", e[e.INT32 = 5] = "INT32", e[e.FIXED64 = 6] = "FIXED64", e[e.FIXED32 = 7] = "FIXED32", e[e.BOOL = 8] = "BOOL", e[e.STRING = 9] = "STRING", e[e.BYTES = 12] = "BYTES", e[e.UINT32 = 13] = "UINT32", e[e.SFIXED32 = 15] = "SFIXED32", e[e.SFIXED64 = 16] = "SFIXED64", e[e.SINT32 = 17] = "SINT32", e[e.SINT64 = 18] = "SINT64";
})(t ||= {});
//#endregion
//#region node_modules/@bufbuild/protobuf/dist/esm/wire/varint.js
function n() {
	let e = this.buf, t = this.pos, n = 0, r = 0;
	for (let i = 0; i < 28; i += 7) {
		let a = e[t++];
		if (n |= (a & 127) << i, !(a & 128)) {
			this.pos = t, this.assertBounds(), this.varint64Lo = n, this.varint64Hi = r;
			return;
		}
	}
	let i = e[t++];
	if (n |= (i & 15) << 28, r = (i & 112) >> 4, !(i & 128)) this.pos = t, this.assertBounds(), this.varint64Lo = n, this.varint64Hi = r;
	else {
		for (let i = 3; i <= 31; i += 7) {
			let a = e[t++];
			if (r |= (a & 127) << i, !(a & 128)) {
				this.pos = t, this.assertBounds(), this.varint64Lo = n, this.varint64Hi = r;
				return;
			}
		}
		throw Error("invalid varint");
	}
}
var r = 4294967296;
function i(e) {
	let t = e[0] === "-";
	t && (e = e.slice(1));
	let n = 1e6, i = 0, a = 0;
	function o(t, o) {
		let s = Number(e.slice(t, o));
		a *= n, i = i * n + s, i >= r && (a += i / r | 0, i %= r);
	}
	return o(-24, -18), o(-18, -12), o(-12, -6), o(-6), t ? l(i, a) : c(i, a);
}
function a(e, t) {
	let n = c(e, t), r = n.hi & 2147483648;
	r && (n = l(n.lo, n.hi));
	let i = o(n.lo, n.hi);
	return r ? "-" + i : i;
}
function o(e, t) {
	if ({lo: e, hi: t} = s(e, t), t <= 2097151) return String(r * t + e);
	let n = e & 16777215, i = (e >>> 24 | t << 8) & 16777215, a = t >> 16 & 65535, o = n + i * 6777216 + a * 6710656, c = i + a * 8147497, l = a * 2, d = 1e7;
	return o >= d && (c += Math.floor(o / d), o %= d), c >= d && (l += Math.floor(c / d), c %= d), l.toString() + u(c) + u(o);
}
function s(e, t) {
	return {
		lo: e >>> 0,
		hi: t >>> 0
	};
}
function c(e, t) {
	return {
		lo: e | 0,
		hi: t | 0
	};
}
function l(e, t) {
	return t = ~t, e ? e = ~e + 1 : t += 1, c(e, t);
}
var u = (e) => {
	let t = String(e);
	return "0000000".slice(t.length) + t;
};
function d(e, t) {
	if (e >>> 0 < 128) t.push(e);
	else if (e >= 0) {
		for (; e > 127;) t.push(e & 127 | 128), e >>>= 7;
		t.push(e);
	} else {
		for (let n = 0; n < 9; n++) t.push(e & 127 | 128), e >>= 7;
		t.push(1);
	}
}
function f() {
	let e = this.buf[this.pos++];
	if (!(e & 128)) return this.assertBounds(), e;
	let t = e & 127;
	if (e = this.buf[this.pos++], t |= (e & 127) << 7, !(e & 128) || (e = this.buf[this.pos++], t |= (e & 127) << 14, !(e & 128)) || (e = this.buf[this.pos++], t |= (e & 127) << 21, !(e & 128))) return this.assertBounds(), t;
	e = this.buf[this.pos++], t |= (e & 15) << 28;
	for (let t = 5; e & 128 && t < 10; t++) e = this.buf[this.pos++];
	if (e & 128) throw Error("invalid varint");
	return this.assertBounds(), t >>> 0;
}
//#endregion
//#region node_modules/@bufbuild/protobuf/dist/esm/proto-int64.js
var p = /*@__PURE__*/ ee();
function ee() {
	let e = /* @__PURE__ */ new DataView(/* @__PURE__ */ new ArrayBuffer(8));
	if (typeof BigInt == "function" && typeof e.getBigInt64 == "function" && typeof e.getBigUint64 == "function" && typeof e.setBigInt64 == "function" && typeof e.setBigUint64 == "function" && (globalThis.Deno || globalThis.Bun || typeof process != "object" || {}.BUF_BIGINT_DISABLE !== "1")) {
		let t = BigInt("-9223372036854775808"), n = BigInt("9223372036854775807"), r = BigInt("0"), i = BigInt("18446744073709551615");
		return {
			zero: BigInt(0),
			supported: !0,
			parse(e) {
				let r = typeof e == "bigint" ? e : BigInt(e);
				if (r > n || r < t) throw Error(`invalid int64: ${e}`);
				return r;
			},
			uParse(e) {
				let t = typeof e == "bigint" ? e : BigInt(e);
				if (t > i || t < r) throw Error(`invalid uint64: ${e}`);
				return t;
			},
			enc(t) {
				return e.setBigInt64(0, this.parse(t), !0), {
					lo: e.getInt32(0, !0),
					hi: e.getInt32(4, !0)
				};
			},
			uEnc(t) {
				return e.setBigInt64(0, this.uParse(t), !0), {
					lo: e.getInt32(0, !0),
					hi: e.getInt32(4, !0)
				};
			},
			dec(t, n) {
				return e.setInt32(0, t, !0), e.setInt32(4, n, !0), e.getBigInt64(0, !0);
			},
			uDec(t, n) {
				return e.setInt32(0, t, !0), e.setInt32(4, n, !0), e.getBigUint64(0, !0);
			}
		};
	}
	return {
		zero: "0",
		supported: !1,
		parse(e) {
			return typeof e != "string" && (e = e.toString()), te(e), e;
		},
		uParse(e) {
			return typeof e != "string" && (e = e.toString()), ne(e), e;
		},
		enc(e) {
			return typeof e != "string" && (e = e.toString()), te(e), i(e);
		},
		uEnc(e) {
			return typeof e != "string" && (e = e.toString()), ne(e), i(e);
		},
		dec(e, t) {
			return a(e, t);
		},
		uDec(e, t) {
			return o(e, t);
		}
	};
}
function te(e) {
	if (!/^-?[0-9]+$/.test(e)) throw Error("invalid int64: " + e);
}
function ne(e) {
	if (!/^[0-9]+$/.test(e)) throw Error("invalid uint64: " + e);
}
//#endregion
//#region node_modules/@bufbuild/protobuf/dist/esm/reflect/scalar.js
function re(e, n) {
	switch (e) {
		case t.STRING: return "";
		case t.BOOL: return !1;
		case t.DOUBLE:
		case t.FLOAT: return 0;
		case t.INT64:
		case t.UINT64:
		case t.SFIXED64:
		case t.FIXED64:
		case t.SINT64: return n ? "0" : p.zero;
		case t.BYTES: return /* @__PURE__ */ new Uint8Array();
		default: return 0;
	}
}
function ie(e, n) {
	switch (e) {
		case t.BOOL: return n === !1;
		case t.STRING: return n === "";
		case t.BYTES: return n instanceof Uint8Array && !n.byteLength;
		case t.DOUBLE:
		case t.FLOAT: return Object.is(n, 0);
		default: return n == 0;
	}
}
//#endregion
//#region node_modules/@bufbuild/protobuf/dist/esm/reflect/unsafe.js
var ae = 2, oe = Symbol.for("reflect unsafe local");
function se(e, t) {
	let n = e[t.localName].case;
	return n === void 0 ? n : t.fields.find((e) => e.localName === n);
}
function ce(e, t) {
	let n = t.localName;
	if (t.oneof) return e[t.oneof.localName].case === n;
	if (t.presence != ae) return e[n] !== void 0 && Object.prototype.hasOwnProperty.call(e, n);
	switch (t.fieldKind) {
		case "list": return e[n].length > 0;
		case "map": return Object.keys(e[n]).length > 0;
		case "scalar": return !ie(t.scalar, e[n]);
		case "enum": return e[n] !== t.enum.values[0].number;
	}
	throw Error("message field with implicit presence");
}
function le(e, t) {
	if (t.oneof) {
		let n = e[t.oneof.localName];
		return n.case === t.localName ? n.value : void 0;
	}
	return e[t.localName];
}
function ue(e, t, n) {
	t.oneof ? e[t.oneof.localName] = {
		case: t.localName,
		value: n
	} : e[t.localName] = n;
}
function de(e, t, n) {
	t === "__proto__" ? Object.defineProperty(e, "__proto__", {
		value: n,
		writable: !0,
		enumerable: !0,
		configurable: !0
	}) : e[t] = n;
}
function fe(e, t) {
	return Object.prototype.hasOwnProperty.call(e, t) ? e[t] : void 0;
}
function pe(e, t) {
	let n = t.localName;
	if (t.oneof) {
		let r = t.oneof.localName;
		e[r].case === n && (e[r] = { case: void 0 });
	} else if (t.presence != ae) delete e[n];
	else switch (t.fieldKind) {
		case "map":
			e[n] = {};
			break;
		case "list":
			e[n] = [];
			break;
		case "enum":
			e[n] = t.enum.values[0].number;
			break;
		case "scalar": e[n] = re(t.scalar, t.longAsString);
	}
}
//#endregion
//#region node_modules/@bufbuild/protobuf/dist/esm/reflect/guard.js
function me(e) {
	return typeof e == "object" && !!e && !Array.isArray(e);
}
function he(e, t) {
	if (me(e) && oe in e && "add" in e && "field" in e && typeof e.field == "function") {
		if (t !== void 0) {
			let n = t, r = e.field();
			return n.listKind == r.listKind && n.scalar === r.scalar && n.message?.typeName === r.message?.typeName && n.enum?.typeName === r.enum?.typeName;
		}
		return !0;
	}
	return !1;
}
function ge(e, t) {
	if (me(e) && oe in e && "has" in e && "field" in e && typeof e.field == "function") {
		if (t !== void 0) {
			let n = t, r = e.field();
			return n.mapKey === r.mapKey && n.mapKind == r.mapKind && n.scalar === r.scalar && n.message?.typeName === r.message?.typeName && n.enum?.typeName === r.enum?.typeName;
		}
		return !0;
	}
	return !1;
}
function _e(e, t) {
	return me(e) && oe in e && "desc" in e && me(e.desc) && e.desc.kind === "message" && (t === void 0 || e.desc.typeName == t.typeName);
}
//#endregion
//#region node_modules/@bufbuild/protobuf/dist/esm/wkt/wrappers.js
function ve(e) {
	return Se(e.$typeName);
}
function ye(e) {
	let t = e.fields[0];
	return Se(e.typeName) && t !== void 0 && t.fieldKind == "scalar" && t.name == "value" && t.number == 1;
}
function be(e) {
	switch (e.typeName) {
		case "google.protobuf.Any":
		case "google.protobuf.Timestamp":
		case "google.protobuf.Duration":
		case "google.protobuf.FieldMask":
		case "google.protobuf.Struct":
		case "google.protobuf.Value":
		case "google.protobuf.ListValue": return !0;
		default: return ye(e);
	}
}
var xe = /*@__PURE__*/ new Set([
	"google.protobuf.DoubleValue",
	"google.protobuf.FloatValue",
	"google.protobuf.Int64Value",
	"google.protobuf.UInt64Value",
	"google.protobuf.Int32Value",
	"google.protobuf.UInt32Value",
	"google.protobuf.BoolValue",
	"google.protobuf.StringValue",
	"google.protobuf.BytesValue"
]);
function Se(e) {
	return xe.has(e);
}
//#endregion
//#region node_modules/@bufbuild/protobuf/dist/esm/create.js
var Ce = 999, we = 998, Te = 2;
function m(t, n) {
	return e(n, t) ? n : De(t)(n);
}
var Ee = /* @__PURE__ */ new WeakMap();
function De(e) {
	let t = Ee.get(e);
	return t === void 0 && (t = Me(e), Ee.set(e, t)), t;
}
var Oe = 0, ke = 1, Ae = 2, je = 3;
function Me(e) {
	let t = e.typeName, { properties: n, prototype: r } = Ne(e);
	return (e) => {
		let i;
		r === void 0 ? i = { $typeName: t } : (i = Object.create(r), i.$typeName = t);
		for (let t = 0; t < n.length; t++) {
			let r = n[t], a = r.name, o = e?.[a];
			switch (r.kind) {
				case Oe:
					o == null ? r.constant !== void 0 && (i[a] = r.constant) : i[a] = r.convert === void 0 ? o : r.convert(o);
					break;
				case ke:
					i[a] = r.convert !== void 0 && Array.isArray(o) ? o.map(r.convert) : o ?? [];
					break;
				case Ae:
					if (r.convert === void 0 || !me(o)) i[a] = o ?? {};
					else {
						let e = {}, t = Object.keys(o);
						for (let n = 0; n < t.length; n++) de(e, t[n], r.convert(o[t[n]]));
						i[a] = e;
					}
					break;
				case je: {
					let e = o;
					if (e?.case != null) {
						let t = r.convert.get(e.case);
						if (t !== void 0) {
							i[a] = {
								case: e.case,
								value: t(e.value)
							};
							break;
						}
					}
					i[a] = { case: void 0 };
					break;
				}
			}
		}
		return i;
	};
}
function Ne(e) {
	let n = [], r = {}, i = Le(e);
	for (let a of e.members) {
		let e = a.localName;
		if (a.kind == "oneof") n.push({
			name: e,
			kind: je,
			constant: void 0,
			convert: Pe(a)
		});
		else switch (a.fieldKind) {
			case "message":
				n.push({
					name: e,
					kind: Oe,
					constant: void 0,
					convert: Fe(a)
				});
				break;
			case "list":
				n.push({
					name: e,
					kind: ke,
					constant: void 0,
					convert: a.listKind == "message" ? Fe(a) ?? ((e) => e) : a.scalar == t.BYTES ? Ie : void 0
				});
				break;
			case "map":
				n.push({
					name: e,
					kind: Ae,
					constant: void 0,
					convert: a.mapKind == "message" ? Fe(a) ?? ((e) => e) : a.scalar == t.BYTES ? Ie : void 0
				});
				break;
			default: {
				let o = Re(a);
				n.push({
					name: e,
					kind: Oe,
					constant: a.presence == Te ? o : void 0,
					convert: a.fieldKind == "scalar" && a.scalar == t.BYTES ? Ie : void 0
				}), i && (r[e] = o);
				break;
			}
		}
	}
	return {
		properties: n,
		prototype: i ? r : void 0
	};
}
function Pe(e) {
	let n = /* @__PURE__ */ new Map();
	for (let r of e.fields) {
		let e;
		r.fieldKind == "message" ? e = Fe(r) : r.fieldKind == "scalar" && r.scalar == t.BYTES && (e = Ie), n.set(r.localName, e ?? ((e) => e));
	}
	return n;
}
function Fe(n) {
	if (n.fieldKind == "message" && !n.oneof && ye(n.message)) return n.message.fields[0].scalar == t.BYTES ? Ie : void 0;
	if (n.message.typeName == "google.protobuf.Struct" && n.parent.typeName !== "google.protobuf.Value") return;
	let r = n.message, i;
	return (t) => !me(t) || e(t, r) ? t : (i ??= De(r), i(t));
}
function Ie(e) {
	return Array.isArray(e) ? new Uint8Array(e) : e;
}
function Le(e) {
	switch (e.file.edition) {
		case Ce: return !1;
		case we: return !0;
		default: return e.fields.some((e) => e.presence != Te && e.fieldKind != "message" && !e.oneof);
	}
}
function Re(e) {
	let t = e.getDefaultValue();
	return t === void 0 ? e.fieldKind == "scalar" ? re(e.scalar, e.longAsString) : e.enum.values[0].number : e.fieldKind == "scalar" && e.longAsString ? t.toString() : t;
}
//#endregion
//#region node_modules/@bufbuild/protobuf/dist/esm/reflect/error.js
var ze = class extends Error {
	constructor(e, t, n = "FieldValueInvalidError") {
		super(t), this.name = n, this.field = () => e;
	}
}, Be;
function Ve(e) {
	Be = Object.assign(Object.assign({}, e), { encodeUtf8Into: e.encodeUtf8Into ?? Ue(e.encodeUtf8.bind(e)) });
}
function He() {
	if (!Be) {
		let e = globalThis;
		if (!e.TextEncoder || !e.TextDecoder) throw Error("encoding API missing: install TextEncoder and TextDecoder on globalThis");
		let t = new e.TextEncoder(), n = new e.TextDecoder(), r, i = {
			encodeUtf8(e) {
				return t.encode(e);
			},
			decodeUtf8(t, i) {
				return i ? (r ||= new e.TextDecoder("utf-8", { fatal: !0 }), r.decode(t)) : n.decode(t);
			},
			checkUtf8(e) {
				try {
					return !0;
				} catch {
					return !1;
				}
			}
		};
		t.encodeInto && (i.encodeUtf8Into = t.encodeInto.bind(t));
		let a = String.prototype.isWellFormed;
		a && (i.checkUtf8 = (e) => a.call(e)), Ve(i);
	}
	return Be;
}
function Ue(e) {
	return (t, n) => {
		let r = e(t);
		return n.set(r), { written: r.byteLength };
	};
}
//#endregion
//#region node_modules/@bufbuild/protobuf/dist/esm/wire/binary-encoding.js
var We;
(function(e) {
	e[e.Varint = 0] = "Varint", e[e.Bit64 = 1] = "Bit64", e[e.LengthDelimited = 2] = "LengthDelimited", e[e.StartGroup = 3] = "StartGroup", e[e.EndGroup = 4] = "EndGroup", e[e.Bit32 = 5] = "Bit32";
})(We ||= {}), new DataView((/* @__PURE__ */ new Uint8Array()).buffer);
var Ge = 32, Ke = class {
	constructor(e, t = He().decodeUtf8) {
		this.decodeUtf8 = t, this.varint64Lo = 0, this.varint64Hi = 0, this.varint64 = n, this.uint32 = f, this.buf = e, this.len = e.length, this.pos = 0, this.view = new DataView(e.buffer, e.byteOffset, e.byteLength);
	}
	tag() {
		let e = this.pos, t = this.uint32(), n = this.pos - e;
		if (n > 5 || n == 5 && this.buf[this.pos - 1] > 15) throw Error("illegal tag: varint overflows uint32");
		let r = t >>> 3, i = t & 7;
		if (r <= 0 || i > 5) throw Error("illegal tag: field no " + r + " wire type " + i);
		return [r, i];
	}
	skip(e, t, n = 100) {
		let r = this.pos;
		switch (e) {
			case We.Varint:
				for (; this.buf[this.pos++] & 128;);
				break;
			case We.Bit64: this.pos += 4;
			case We.Bit32:
				this.pos += 4;
				break;
			case We.LengthDelimited:
				let r = this.uint32();
				this.pos += r;
				break;
			case We.StartGroup:
				if (n <= 0) throw Error("maximum recursion depth reached");
				for (;;) {
					let [e, r] = this.tag();
					if (r === We.EndGroup) {
						if (t !== void 0 && e !== t) throw Error("invalid end group tag");
						break;
					}
					this.skip(r, e, n - 1);
				}
				break;
			default: throw Error("cant skip wire type " + e);
		}
		return this.assertBounds(), this.buf.subarray(r, this.pos);
	}
	assertBounds() {
		if (this.pos > this.len) throw RangeError("premature EOF");
	}
	int32() {
		return this.uint32() | 0;
	}
	sint32() {
		let e = this.uint32();
		return e >>> 1 ^ -(e & 1);
	}
	int64() {
		return this.varint64(), p.dec(this.varint64Lo, this.varint64Hi);
	}
	uint64() {
		return this.varint64(), p.uDec(this.varint64Lo, this.varint64Hi);
	}
	sint64() {
		this.varint64();
		let e = this.varint64Lo, t = this.varint64Hi, n = -(e & 1);
		return e = (e >>> 1 | (t & 1) << 31) ^ n, t = t >>> 1 ^ n, p.dec(e, t);
	}
	bool() {
		let e = this.buf[this.pos];
		return e < 128 ? (this.pos++, e !== 0) : (this.varint64(), this.varint64Lo !== 0 || this.varint64Hi !== 0);
	}
	fixed32() {
		return this.view.getUint32((this.pos += 4) - 4, !0);
	}
	sfixed32() {
		return this.view.getInt32((this.pos += 4) - 4, !0);
	}
	fixed64() {
		return p.uDec(this.sfixed32(), this.sfixed32());
	}
	sfixed64() {
		return p.dec(this.sfixed32(), this.sfixed32());
	}
	float() {
		return this.view.getFloat32((this.pos += 4) - 4, !0);
	}
	double() {
		return this.view.getFloat64((this.pos += 8) - 8, !0);
	}
	bytes() {
		let e = this.uint32(), t = this.pos;
		return this.pos += e, this.assertBounds(), this.buf.subarray(t, t + e);
	}
	string(e) {
		let t = this.bytes(), n = t.length;
		if (n <= Ge) {
			let r = Array(n);
			for (let i = 0; i < n; i++) {
				let n = t[i];
				if (n > 127) return this.decodeUtf8(t, e);
				r[i] = n;
			}
			return String.fromCharCode.apply(String, r);
		}
		return this.decodeUtf8(t, e);
	}
};
//#endregion
//#region node_modules/@bufbuild/protobuf/dist/esm/reflect/reflect-check.js
function qe(e, t) {
	let n = e.fieldKind == "list" ? he(t, e) : e.fieldKind == "map" ? ge(t, e) : Xe(e, t);
	if (n === !0) return;
	let r;
	switch (e.fieldKind) {
		case "list":
			r = `expected ${tt(e)}, got ${$e(t)}`;
			break;
		case "map":
			r = `expected ${nt(e)}, got ${$e(t)}`;
			break;
		default: r = Qe(e, t, n);
	}
	return new ze(e, r);
}
function Je(e, t, n) {
	let r = Xe(e, n);
	if (r !== !0) return new ze(e, `list item #${t + 1}: ${Qe(e, n, r)}`);
}
function Ye(e, t, n) {
	let r = Ze(e.mapKey)(t);
	if (r !== !0) return new ze(e, `invalid map key: ${Qe({ scalar: e.mapKey }, t, r)}`);
	let i = Xe(e, n);
	if (i !== !0) return new ze(e, `map entry ${$e(t)}: ${Qe(e, n, i)}`);
}
function Xe(e, n) {
	return e.scalar === void 0 ? e.enum === void 0 ? _e(n, e.message) : e.enum.open ? Ze(t.INT32)(n) : e.enum.values.some((e) => e.number === n) : Ze(e.scalar)(n);
}
function Ze(e) {
	switch (e) {
		case t.DOUBLE: return (e) => typeof e == "number";
		case t.FLOAT: return (e) => typeof e == "number" ? !Number.isFinite(Math.fround(e)) && Number.isFinite(e) ? `${e.toFixed()} out of range` : !0 : !1;
		case t.INT32:
		case t.SFIXED32:
		case t.SINT32: return (e) => typeof e != "number" || !Number.isInteger(e) ? !1 : e > 2147483647 || e < -2147483648 ? `${e.toFixed()} out of range` : !0;
		case t.FIXED32:
		case t.UINT32: return (e) => typeof e != "number" || !Number.isInteger(e) ? !1 : e > 4294967295 || e < 0 ? `${e.toFixed()} out of range` : !0;
		case t.BOOL: return (e) => typeof e == "boolean";
		case t.STRING: return (e) => typeof e == "string" ? He().checkUtf8(e) || "invalid UTF8" : !1;
		case t.BYTES: return (e) => e instanceof Uint8Array;
		case t.INT64:
		case t.SFIXED64:
		case t.SINT64: return (e) => {
			if (typeof e == "bigint" || typeof e == "number" || typeof e == "string" && e.length > 0) try {
				return p.parse(e), !0;
			} catch {
				return `${e} out of range`;
			}
			return !1;
		};
		case t.FIXED64:
		case t.UINT64: return (e) => {
			if (typeof e == "bigint" || typeof e == "number" || typeof e == "string" && e.length > 0) try {
				return p.uParse(e), !0;
			} catch {
				return `${e} out of range`;
			}
			return !1;
		};
	}
}
function Qe(e, t, n) {
	return n = typeof n == "string" ? `: ${n}` : `, got ${$e(t)}`, e.scalar === void 0 ? e.enum === void 0 ? `expected ${et(e.message)}` + n : `expected ${e.enum.toString()}` + n : `expected ${rt(e.scalar)}` + n;
}
function $e(t) {
	switch (typeof t) {
		case "object": return t === null ? "null" : t instanceof Uint8Array ? `Uint8Array(${t.length})` : Array.isArray(t) ? `Array(${t.length})` : he(t) ? tt(t.field()) : ge(t) ? nt(t.field()) : _e(t) ? et(t.desc) : e(t) ? `message ${t.$typeName}` : "object";
		case "string": return t.length > 30 ? "string" : `"${t.split("\"").join("\\\"")}"`;
		case "boolean": return String(t);
		case "number": return String(t);
		case "bigint": return String(t) + "n";
		default: return typeof t;
	}
}
function et(e) {
	return `ReflectMessage (${e.typeName})`;
}
function tt(e) {
	switch (e.listKind) {
		case "message": return `ReflectList (${e.message.toString()})`;
		case "enum": return `ReflectList (${e.enum.toString()})`;
		case "scalar": return `ReflectList (${t[e.scalar]})`;
	}
}
function nt(e) {
	switch (e.mapKind) {
		case "message": return `ReflectMap (${t[e.mapKey]}, ${e.message.toString()})`;
		case "enum": return `ReflectMap (${t[e.mapKey]}, ${e.enum.toString()})`;
		case "scalar": return `ReflectMap (${t[e.mapKey]}, ${t[e.scalar]})`;
	}
}
function rt(e) {
	switch (e) {
		case t.STRING: return "string";
		case t.BOOL: return "boolean";
		case t.INT64:
		case t.SINT64:
		case t.SFIXED64: return "bigint (int64)";
		case t.UINT64:
		case t.FIXED64: return "bigint (uint64)";
		case t.BYTES: return "Uint8Array";
		case t.DOUBLE: return "number (float64)";
		case t.FLOAT: return "number (float32)";
		case t.FIXED32:
		case t.UINT32: return "number (uint32)";
		case t.INT32:
		case t.SFIXED32:
		case t.SINT32: return "number (int32)";
	}
}
//#endregion
//#region node_modules/@bufbuild/protobuf/dist/esm/reflect/message.js
var it = 0;
function at(e) {
	if (ot(e)) return {
		toMessage: (e) => st(e),
		toLocal: (e) => ct(e)
	};
	if (e.fieldKind == "message" && !e.oneof && ye(e.message)) {
		let t = e.message, n = t.fields[0].localName;
		return {
			toMessage: (e) => {
				let r = m(t);
				return e !== void 0 && (r[n] = e), r;
			},
			toLocal: (e) => e[n]
		};
	}
	let t = e.message;
	return {
		toMessage: (e) => e === void 0 ? m(t) : e,
		toLocal: (e) => e
	};
}
function ot(e) {
	return e.message.typeName == "google.protobuf.Struct" && e.parent.typeName != "google.protobuf.Value";
}
function st(e) {
	let t = {
		$typeName: "google.protobuf.Struct",
		fields: {}
	};
	if (me(e)) for (let n of Object.keys(e)) de(t.fields, n, ut(e[n]));
	return t;
}
function ct(e) {
	let t = {};
	for (let n of Object.keys(e.fields)) de(t, n, lt(e.fields[n]));
	return t;
}
function lt(e) {
	switch (e.kind.case) {
		case "structValue": return ct(e.kind.value);
		case "listValue": return e.kind.value.values.map(lt);
		case "nullValue":
		case void 0: return null;
		default: return e.kind.value;
	}
}
function ut(e) {
	let t = {
		$typeName: "google.protobuf.Value",
		kind: { case: void 0 }
	};
	switch (typeof e) {
		case "number":
			t.kind = {
				case: "numberValue",
				value: e
			};
			break;
		case "string":
			t.kind = {
				case: "stringValue",
				value: e
			};
			break;
		case "boolean":
			t.kind = {
				case: "boolValue",
				value: e
			};
			break;
		case "object": if (e === null) t.kind = {
			case: "nullValue",
			value: it
		};
		else if (Array.isArray(e)) {
			let n = {
				$typeName: "google.protobuf.ListValue",
				values: []
			};
			if (Array.isArray(e)) for (let t of e) n.values.push(ut(t));
			t.kind = {
				case: "listValue",
				value: n
			};
		} else t.kind = {
			case: "structValue",
			value: st(e)
		};
	}
	return t;
}
//#endregion
//#region node_modules/@bufbuild/protobuf/dist/esm/reflect/reflect.js
function dt(e, t, n = !0) {
	return new pt(e, t, n);
}
var ft = /* @__PURE__ */ new WeakMap(), pt = class {
	get sortedFields() {
		let e = ft.get(this.desc);
		if (e) return e;
		let t = this.desc.fields.concat().sort((e, t) => e.number - t.number);
		return ft.set(this.desc, t), t;
	}
	constructor(e, t, n = !0) {
		this.lists = /* @__PURE__ */ new Map(), this.maps = /* @__PURE__ */ new Map(), this.check = n, this.desc = e, this.message = this[oe] = t ?? m(e), this.fields = e.fields, this.oneofs = e.oneofs, this.members = e.members;
	}
	findNumber(e) {
		return this._fieldsByNumber ||= new Map(this.desc.fields.map((e) => [e.number, e])), this._fieldsByNumber.get(e);
	}
	oneofCase(e) {
		return mt(this.message, e), se(this.message, e);
	}
	isSet(e) {
		return mt(this.message, e), ce(this.message, e);
	}
	clear(e) {
		mt(this.message, e), pe(this.message, e);
	}
	get(e) {
		mt(this.message, e);
		let t = le(this.message, e);
		switch (e.fieldKind) {
			case "list":
				let n = this.lists.get(e);
				return (!n || n[oe] !== t) && this.lists.set(e, n = new ht(e, t, this.check)), n;
			case "map":
				let r = this.maps.get(e);
				return (!r || r[oe] !== t) && this.maps.set(e, r = new gt(e, t, this.check)), r;
			case "message": return vt(e, t, this.check);
			case "scalar": return t === void 0 ? re(e.scalar, !1) : Tt(e, t);
			case "enum": return t ?? e.enum.values[0].number;
		}
	}
	set(e, t) {
		if (mt(this.message, e), this.check) {
			let n = qe(e, t);
			if (n) throw n;
		}
		let n;
		n = e.fieldKind == "message" ? _t(e, t) : ge(t) || he(t) ? t[oe] : Et(e, t), ue(this.message, e, n);
	}
	getUnknown() {
		return this.message.$unknown;
	}
	setUnknown(e) {
		this.message.$unknown = e;
	}
};
function mt(e, t) {
	if (t.parent.typeName !== e.$typeName) throw new ze(t, `cannot use ${t.toString()} with message ${e.$typeName}`, "ForeignFieldError");
}
var ht = class {
	field() {
		return this._field;
	}
	get size() {
		return this._arr.length;
	}
	constructor(e, t, n) {
		this._field = e, this._arr = this[oe] = t, this.check = n;
	}
	get(e) {
		let t = this._arr[e];
		return t === void 0 ? void 0 : bt(this._field, t, this.check);
	}
	set(e, t) {
		if (e < 0 || e >= this._arr.length) throw new ze(this._field, `list item #${e + 1}: out of range`);
		if (this.check) {
			let n = Je(this._field, e, t);
			if (n) throw n;
		}
		this._arr[e] = yt(this._field, t);
	}
	add(e) {
		if (this.check) {
			let t = Je(this._field, this._arr.length, e);
			if (t) throw t;
		}
		this._arr.push(yt(this._field, e));
	}
	clear() {
		this._arr.splice(0, this._arr.length);
	}
	[Symbol.iterator]() {
		return this.values();
	}
	keys() {
		return this._arr.keys();
	}
	*values() {
		for (let e of this._arr) yield bt(this._field, e, this.check);
	}
	*entries() {
		for (let e = 0; e < this._arr.length; e++) yield [e, bt(this._field, this._arr[e], this.check)];
	}
}, gt = class {
	constructor(e, t, n = !0) {
		this.obj = this[oe] = t ?? {}, this.check = n, this._field = e;
	}
	field() {
		return this._field;
	}
	set(e, t) {
		if (this.check) {
			let n = Ye(this._field, e, t);
			if (n) throw n;
		}
		return de(this.obj, Ct(e), xt(this._field, t)), this;
	}
	delete(e) {
		let t = Ct(e), n = Object.prototype.hasOwnProperty.call(this.obj, t);
		return n && delete this.obj[t], n;
	}
	clear() {
		for (let e of Object.keys(this.obj)) delete this.obj[e];
	}
	get(e) {
		let t = fe(this.obj, Ct(e));
		return t !== void 0 && (t = St(this._field, t, this.check)), t;
	}
	has(e) {
		return Object.prototype.hasOwnProperty.call(this.obj, Ct(e));
	}
	*keys() {
		for (let e of Object.keys(this.obj)) yield wt(e, this._field.mapKey);
	}
	*entries() {
		for (let e of Object.entries(this.obj)) yield [wt(e[0], this._field.mapKey), St(this._field, e[1], this.check)];
	}
	[Symbol.iterator]() {
		return this.entries();
	}
	get size() {
		return Object.keys(this.obj).length;
	}
	*values() {
		for (let e of Object.values(this.obj)) yield St(this._field, e, this.check);
	}
	forEach(e, t) {
		for (let n of this.entries()) e.call(t, n[1], n[0], this);
	}
};
function _t(e, t) {
	return _e(t) ? ve(t.message) && !e.oneof && e.fieldKind == "message" ? t.message.value : t.desc.typeName == "google.protobuf.Struct" && e.parent.typeName != "google.protobuf.Value" ? ct(t.message) : t.message : t;
}
function vt(e, t, n) {
	return t !== void 0 && (ye(e.message) && !e.oneof && e.fieldKind == "message" ? t = {
		$typeName: e.message.typeName,
		value: Tt(e.message.fields[0], t)
	} : e.message.typeName == "google.protobuf.Struct" && e.parent.typeName != "google.protobuf.Value" && me(t) && (t = st(t))), new pt(e.message, t, n);
}
function yt(e, t) {
	return e.listKind == "message" ? _t(e, t) : Et(e, t);
}
function bt(e, t, n) {
	return e.listKind == "message" ? vt(e, t, n) : Tt(e, t);
}
function xt(e, t) {
	return e.mapKind == "message" ? _t(e, t) : Et(e, t);
}
function St(e, t, n) {
	return e.mapKind == "message" ? vt(e, t, n) : t;
}
function Ct(e) {
	return typeof e == "string" || typeof e == "number" ? e : String(e);
}
function wt(e, n) {
	switch (n) {
		case t.STRING: return e;
		case t.INT32:
		case t.FIXED32:
		case t.UINT32:
		case t.SFIXED32:
		case t.SINT32: {
			let t = Number.parseInt(e);
			if (Number.isFinite(t)) return t;
			break;
		}
		case t.BOOL:
			switch (e) {
				case "true": return !0;
				case "false": return !1;
			}
			break;
		case t.UINT64:
		case t.FIXED64:
			try {
				return p.uParse(e);
			} catch {}
			break;
		default: try {
			return p.parse(e);
		} catch {}
	}
	return e;
}
function Tt(e, n) {
	switch (e.scalar) {
		case t.INT64:
		case t.SFIXED64:
		case t.SINT64:
			"longAsString" in e && e.longAsString && typeof n == "string" && (n = p.parse(n));
			break;
		case t.FIXED64:
		case t.UINT64: "longAsString" in e && e.longAsString && typeof n == "string" && (n = p.uParse(n));
	}
	return n;
}
function Et(e, n) {
	switch (e.scalar) {
		case t.INT64:
		case t.SFIXED64:
		case t.SINT64:
			"longAsString" in e && e.longAsString ? n = String(n) : (typeof n == "string" || typeof n == "number") && (n = p.parse(n));
			break;
		case t.FIXED64:
		case t.UINT64: "longAsString" in e && e.longAsString ? n = String(n) : (typeof n == "string" || typeof n == "number") && (n = p.uParse(n));
	}
	return n;
}
Uint8Array.prototype.setFromBase64;
var Dt = Uint8Array.prototype.toBase64, Ot = {
	std: {
		alphabet: "base64",
		omitPadding: !1
	},
	std_raw: {
		alphabet: "base64",
		omitPadding: !0
	},
	url: {
		alphabet: "base64url",
		omitPadding: !0
	}
};
function kt(e, t = "std") {
	if (Dt) return Dt.call(e, Ot[t]);
	let n = Mt(t), r = t == "std", i = "", a = 0, o, s = 0;
	for (let t = 0; t < e.length; t++) switch (o = e[t], a) {
		case 0:
			i += n[o >> 2], s = (o & 3) << 4, a = 1;
			break;
		case 1:
			i += n[s | o >> 4], s = (o & 15) << 2, a = 2;
			break;
		case 2: i += n[s | o >> 6], i += n[o & 63], a = 0;
	}
	return a && (i += n[s], r && (i += "=", a == 1 && (i += "="))), i;
}
var At, jt;
function Mt(e) {
	return At || (At = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/".split(""), jt = At.slice(0, -2).concat("-", "_")), e == "url" ? jt : At;
}
//#endregion
//#region node_modules/@bufbuild/protobuf/dist/esm/reflect/names.js
function Nt(e) {
	let t = !1, n = [];
	for (let r = 0; r < e.length; r++) {
		let i = e.charAt(r);
		switch (i) {
			case "_":
				t = !0;
				break;
			case "0":
			case "1":
			case "2":
			case "3":
			case "4":
			case "5":
			case "6":
			case "7":
			case "8":
			case "9":
				n.push(i), t = !1;
				break;
			default: t && (t = !1, i = i.toUpperCase()), n.push(i);
		}
	}
	return n.join("");
}
function Pt(e) {
	return e.replace(/[A-Z]/g, (e) => "_" + e.toLowerCase());
}
//#endregion
//#region node_modules/@bufbuild/protobuf/dist/esm/from-binary.js
function Ft(e) {
	return Object.assign(Object.assign({
		readUnknownFields: !0,
		recursionLimit: 100
	}, e), { depth: 0 });
}
function It(e, t, n) {
	let r = m(e);
	return Rt(e).read(r, new Ke(t), Ft(n), t.byteLength), r;
}
var Lt = /* @__PURE__ */ new WeakMap();
function Rt(e) {
	let t = Lt.get(e);
	return t === void 0 && (t = zt(e)), t;
}
function zt(e) {
	let t = String(e), n = /* @__PURE__ */ new Map(), r = {
		read: Bt(t, n),
		readGroup: Vt(t, n)
	};
	Lt.set(e, r);
	for (let t of e.fields) n.set(t.number, Ut(t));
	return r;
}
function Bt(e, t) {
	return (n, r, i, a) => {
		if (++i.depth > i.recursionLimit) throw Error(`cannot decode ${e} from binary: maximum recursion depth of ${i.recursionLimit} reached`);
		let o = r.pos + a, s = n.$unknown ?? [];
		for (; r.pos < o;) {
			let [e, a] = r.tag(), o = t.get(e);
			if (o === void 0) {
				let t = r.skip(a, e, i.recursionLimit - i.depth);
				i.readUnknownFields && s.push({
					no: e,
					wireType: a,
					data: t
				});
			} else o(n, r, i, a);
		}
		s.length > 0 && (n.$unknown = s), i.depth--;
	};
}
function Vt(e, t) {
	return (n, r, i, a) => {
		if (++i.depth > i.recursionLimit) throw Error(`cannot decode ${e} from binary: maximum recursion depth of ${i.recursionLimit} reached`);
		let o, s, c = n.$unknown ?? [];
		for (; r.pos < r.len && ([o, s] = r.tag(), s != We.EndGroup);) {
			let e = t.get(o);
			if (e === void 0) {
				let e = r.skip(s, o, i.recursionLimit - i.depth);
				i.readUnknownFields && c.push({
					no: o,
					wireType: s,
					data: e
				});
			} else e(n, r, i, s);
		}
		if (s != We.EndGroup || o !== a) throw Error("invalid end group tag");
		c.length > 0 && (n.$unknown = c), i.depth--;
	};
}
function Ht(e, t, n, r, i) {
	Ut(n)(e[oe], t, i, r);
}
function Ut(e) {
	switch (e.fieldKind) {
		case "scalar": return Wt(e);
		case "enum": return Gt(e);
		case "message": return Kt(e);
		case "list": return Jt(e);
		case "map": return Yt(e);
	}
}
function Wt(e) {
	let t = Xt(e.scalar, e.utf8Validation, e.longAsString), n = e.localName;
	if (e.oneof) {
		let r = e.oneof.localName;
		return (e, i) => {
			e[r] = {
				case: n,
				value: t(i)
			};
		};
	}
	return (e, r) => {
		e[n] = t(r);
	};
}
function Gt(e) {
	let t = e.localName, n = e.oneof?.localName;
	if (e.enum.open) return n === void 0 ? (e, n) => {
		e[t] = n.int32();
	} : (e, r) => {
		e[n] = {
			case: t,
			value: r.int32()
		};
	};
	let r = e.enum.values, i = e.number;
	return (e, a, o, s) => {
		let c = a.int32();
		if (r.some((e) => e.number === c)) n === void 0 ? e[t] = c : e[n] = {
			case: t,
			value: c
		};
		else if (o.readUnknownFields) {
			let t = [];
			d(c, t);
			let n = e.$unknown ?? [];
			n.push({
				no: i,
				wireType: s,
				data: new Uint8Array(t)
			}), e.$unknown = n;
		}
	};
}
function Kt(e) {
	let t = e.localName, { toMessage: n, toLocal: r } = at(e), i = qt(e);
	if (e.oneof) {
		let a = e.oneof.localName;
		return (e, o, s) => {
			let c = e[a], l = n(c.case === t ? c.value : void 0);
			i(l, o, s), e[a] = {
				case: t,
				value: r(l)
			};
		};
	}
	return (e, a, o) => {
		let s = n(e[t]);
		i(s, a, o), e[t] = r(s);
	};
}
function qt(e) {
	let t = Rt(e.message);
	if (e.delimitedEncoding) {
		let n = e.number;
		return (e, r, i) => t.readGroup(e, r, i, n);
	}
	return (e, n, r) => t.read(e, n, r, n.uint32());
}
function Jt(e) {
	let n = e.localName;
	if (e.listKind == "message") {
		let { toMessage: t, toLocal: r } = at(e), i = qt(e);
		return (e, a, o) => {
			let s = t(void 0);
			i(s, a, o), e[n].push(r(s));
		};
	}
	let r = e.listKind == "enum" ? t.INT32 : e.scalar, i = e.listKind == "scalar" && e.longAsString, a = Xt(r, e.utf8Validation, i), o = r != t.STRING && r != t.BYTES;
	return (e, t, r, i) => {
		let s = e[n];
		if (i == We.LengthDelimited && o) {
			let e = t.uint32() + t.pos;
			for (; t.pos < e;) s.push(a(t));
		} else s.push(a(t));
	};
}
function Yt(e) {
	let n = e.localName, r = Xt(e.mapKey, e.utf8Validation, !1), i = re(e.mapKey, !1), a, o;
	switch (e.mapKind) {
		case "scalar": {
			let n = e.scalar, r = Xt(n, e.utf8Validation, !1);
			if (a = (e) => r(e), n == t.BYTES) o = () => /* @__PURE__ */ new Uint8Array();
			else {
				let e = re(n, !1);
				o = () => e;
			}
			break;
		}
		case "enum": {
			let t = e.enum.values[0].number;
			a = (e) => e.int32(), o = () => t;
			break;
		}
		case "message": {
			let { toMessage: t, toLocal: n } = at(e), r = Rt(e.message).read;
			a = (e, i, a) => {
				let o = t(a);
				return r(o, e, i, e.uint32()), n(o);
			}, o = () => n(t(void 0));
			break;
		}
	}
	return (e, t, s) => {
		let c = e[n], l, u, d = t.uint32(), f = t.pos + d;
		for (; t.pos < f;) {
			let [e] = t.tag();
			switch (e) {
				case 1:
					l = r(t);
					break;
				case 2: u = a(t, s, u);
			}
		}
		l === void 0 && (l = i), u === void 0 && (u = o()), de(c, l, u);
	};
}
function Xt(e, n, r) {
	switch (e) {
		case t.STRING: return (e) => e.string(n);
		case t.BOOL: return (e) => e.bool();
		case t.DOUBLE: return (e) => e.double();
		case t.FLOAT: return (e) => e.float();
		case t.INT32: return (e) => e.int32();
		case t.INT64: return r ? (e) => String(e.int64()) : (e) => e.int64();
		case t.UINT64: return r ? (e) => String(e.uint64()) : (e) => e.uint64();
		case t.FIXED64: return r ? (e) => String(e.fixed64()) : (e) => e.fixed64();
		case t.BYTES: return (e) => e.bytes();
		case t.FIXED32: return (e) => e.fixed32();
		case t.SFIXED32: return (e) => e.sfixed32();
		case t.SFIXED64: return r ? (e) => String(e.sfixed64()) : (e) => e.sfixed64();
		case t.SINT64: return r ? (e) => String(e.sint64()) : (e) => e.sint64();
		case t.UINT32: return (e) => e.uint32();
		case t.SINT32: return (e) => e.sint32();
	}
}
//#endregion
//#region node_modules/@bufbuild/protobuf/dist/esm/wkt/any.js
function Zt(e, t) {
	return e.typeUrl !== "" && (typeof t == "string" ? t : t.typeName) === $t(e.typeUrl);
}
function Qt(e, t) {
	if (e.typeUrl === "") return;
	let n = t.kind == "message" ? t : t.getMessage($t(e.typeUrl));
	if (n && Zt(e, n)) return It(n, e.value);
}
function $t(e) {
	let t = e.lastIndexOf("/"), n = t >= 0 ? e.substring(t + 1) : e;
	if (!n.length) throw Error(`invalid type url: ${e}`);
	return n;
}
//#endregion
//#region node_modules/@bufbuild/protobuf/dist/esm/extensions.js
function en(e, t, n) {
	rn(t, e);
	let r = tn(e.$unknown, t), [i, a, o] = nn(t), s = Ft(n);
	for (let e of r) Ht(i, new Ke(e.data), a, e.wireType, s);
	return o();
}
function tn(e, t) {
	if (e === void 0) return [];
	if (t.fieldKind === "enum" || t.fieldKind === "scalar") {
		for (let n = e.length - 1; n >= 0; --n) if (e[n].no == t.number) return [e[n]];
		return [];
	}
	return e.filter((e) => e.no === t.number);
}
function nn(e, t) {
	let n = e.typeName, r = Object.assign(Object.assign({}, e), {
		kind: "field",
		parent: e.extendee,
		localName: n
	}), i = Object.assign(Object.assign({}, e.extendee), {
		fields: [r],
		members: [r],
		oneofs: []
	}), a = m(i, t === void 0 ? void 0 : { [n]: t });
	return [
		dt(i, a),
		r,
		() => {
			let t = a[n];
			if (t === void 0) {
				let t = e.message;
				return ye(t) ? re(t.fields[0].scalar, t.fields[0].longAsString) : m(t);
			}
			return t;
		}
	];
}
function rn(e, t) {
	if (e.extendee.typeName != t.$typeName) throw Error(`extension ${e.typeName} can only be applied to message ${e.extendee.typeName}`);
}
//#endregion
//#region node_modules/@bufbuild/protobuf/dist/esm/wkt/json.js
var an = /*@__PURE__*/ Date.parse("0001-01-01T00:00:00Z"), on = /*@__PURE__*/ Date.parse("9999-12-31T23:59:59Z"), sn = 3, cn = 2, ln = {
	alwaysEmitImplicit: !1,
	enumAsInteger: !1,
	useProtoFieldName: !1
};
function un(e) {
	return e ? Object.assign(Object.assign({}, ln), e) : ln;
}
function dn(e, t, n) {
	return pn(e)(un(n), t);
}
var fn = /* @__PURE__ */ new WeakMap();
function pn(e) {
	let t = fn.get(e);
	return t === void 0 && (t = mn(e)), t;
}
function mn(e) {
	let t = e.typeName, n = gn(e);
	if (n !== void 0) {
		let r = e.fields[0], i = (e, i) => {
			if (i.$typeName !== t && r !== void 0) throw new ze(r, `cannot use ${r} with message ${i.$typeName}`, "ForeignFieldError");
			return n(e, i);
		};
		return fn.set(e, i), i;
	}
	let r = e.fields.concat().sort((e, t) => e.number - t.number), i = r[0], a = [], o = (n, r) => {
		if (r.$typeName !== t && i !== void 0) throw new ze(i, `cannot use ${i} with message ${r.$typeName}`, "ForeignFieldError");
		let o = {};
		for (let e = 0; e < a.length; e++) a[e](n, r, o);
		return n.registry && An(o, n, n.registry, r, e), o;
	};
	fn.set(e, o);
	for (let e of r) {
		let t = _n(e);
		a.push(e.jsonName === "__proto__" || e.name === "__proto__" ? hn(t) : t);
	}
	return o;
}
function hn(e) {
	return (t, n, r) => {
		let i = Object.create(null);
		e(t, n, i);
		for (let e of Object.keys(i)) de(r, e, i[e]);
	};
}
function gn(e) {
	if (e.typeName.startsWith("google.protobuf.")) switch (e.typeName) {
		case "google.protobuf.Any": return (e, t) => jn(t, e);
		case "google.protobuf.Timestamp": return (e, t) => Ln(t);
		case "google.protobuf.Duration": return (e, t) => Mn(t);
		case "google.protobuf.FieldMask": return (e, t) => Nn(t);
		case "google.protobuf.Struct": return (e, t) => Pn(t);
		case "google.protobuf.Value": return (e, t) => Fn(t);
		case "google.protobuf.ListValue": return (e, t) => In(t);
		default:
			if (ye(e)) {
				let t = e.fields[0], n = t.localName, r = re(t.scalar, !1), i = On(t);
				return (e, t) => {
					let a = t[n];
					return i(e, a === void 0 ? r : a);
				};
			}
			return;
	}
}
function _n(e) {
	switch (e.fieldKind) {
		case "scalar":
		case "enum":
		case "message": return vn(e);
		case "list":
		case "map": {
			let t = e.fieldKind == "list" ? Sn(e) : wn(e), n = e.name, r = e.jsonName, i = e.localName;
			return (e, a, o) => {
				let s = t(e, a[i]);
				s !== void 0 && (o[e.useProtoFieldName ? n : r] = s);
			};
		}
	}
}
function vn(e) {
	let n = bn(e), r = e.name, i = e.jsonName, a = e.localName;
	if (e.oneof) {
		let t = e.oneof.localName;
		return (e, o, s) => {
			let c = o[t];
			c.case === a && (s[e.useProtoFieldName ? r : i] = n(e, c.value));
		};
	}
	if (e.presence != cn) {
		let t = e.presence == sn ? `cannot encode ${e} to JSON: required field not set` : void 0;
		return (e, o, s) => {
			let c = o[a];
			if (c !== void 0 && Object.prototype.hasOwnProperty.call(o, a)) s[e.useProtoFieldName ? r : i] = n(e, c);
			else if (t !== void 0) throw Error(t);
		};
	}
	if (e.fieldKind == "enum") {
		let t = e.enum.values[0].number;
		return (e, o, s) => {
			let c = o[a];
			(c !== t || e.alwaysEmitImplicit) && (s[e.useProtoFieldName ? r : i] = n(e, c));
		};
	}
	switch (e.scalar) {
		case t.BOOL: return (e, t, o) => {
			let s = t[a];
			(s !== !1 || e.alwaysEmitImplicit) && (o[e.useProtoFieldName ? r : i] = n(e, s));
		};
		case t.STRING: return (e, t, o) => {
			let s = t[a];
			(s !== "" || e.alwaysEmitImplicit) && (o[e.useProtoFieldName ? r : i] = n(e, s));
		};
		case t.BYTES: return (e, t, o) => {
			let s = t[a];
			(!(s instanceof Uint8Array) || s.byteLength > 0 || e.alwaysEmitImplicit) && (o[e.useProtoFieldName ? r : i] = n(e, s));
		};
		case t.DOUBLE:
		case t.FLOAT: return (e, t, o) => {
			let s = t[a];
			(!Object.is(s, 0) || e.alwaysEmitImplicit) && (o[e.useProtoFieldName ? r : i] = n(e, s));
		};
		default: return (e, t, o) => {
			let s = t[a];
			(s != 0 || e.alwaysEmitImplicit) && (o[e.useProtoFieldName ? r : i] = n(e, s));
		};
	}
}
function yn(e) {
	switch (e.fieldKind) {
		case "scalar":
		case "enum":
		case "message": return bn(e);
		case "list": return Sn(e);
		case "map": return wn(e);
	}
}
function bn(e) {
	switch (e.fieldKind) {
		case "scalar": return On(e);
		case "enum": return En(e);
		case "message": return xn(e);
	}
}
function xn(e) {
	let { toMessage: t } = at(e), n = pn(e.message);
	return (e, r) => n(e, t(r));
}
function Sn(e) {
	let t = Cn(e);
	return (e, n) => {
		let r = n;
		if (r.length == 0 && !e.alwaysEmitImplicit) return;
		let i = [];
		for (let n = 0; n < r.length; n++) i.push(t(e, r[n]));
		return i;
	};
}
function Cn(e) {
	switch (e.listKind) {
		case "scalar": return On(e);
		case "enum": return En(e);
		case "message": return xn(e);
	}
}
function wn(e) {
	let t = Tn(e);
	return (e, n) => {
		let r = n, i = Object.keys(r);
		if (i.length == 0 && !e.alwaysEmitImplicit) return;
		let a = {};
		for (let n = 0; n < i.length; n++) {
			let o = i[n];
			de(a, o, t(e, r[o]));
		}
		return a;
	};
}
function Tn(e) {
	switch (e.mapKind) {
		case "scalar": return On(e);
		case "enum": return En(e);
		case "message": return xn(e);
	}
}
function En(e) {
	let t = e.enum;
	return t.typeName == "google.protobuf.NullValue" ? (e, n) => {
		if (typeof n != "number") throw Dn(t, n);
		return null;
	} : (e, n) => {
		if (typeof n != "number") throw Dn(t, n);
		return e.enumAsInteger ? n : t.value[n]?.jsonName ?? n;
	};
}
function Dn(e, t) {
	return /* @__PURE__ */ Error(`cannot encode ${e} to JSON: expected number, got ${$e(t)}`);
}
function On(e) {
	switch (e.scalar) {
		case t.INT32:
		case t.SFIXED32:
		case t.SINT32:
		case t.FIXED32:
		case t.UINT32: return (t, n) => {
			if (typeof n != "number") throw kn(e, n);
			return n;
		};
		case t.FLOAT:
		case t.DOUBLE: return (t, n) => {
			if (typeof n != "number") throw kn(e, n);
			return Number.isNaN(n) ? "NaN" : n === Infinity ? "Infinity" : n === -Infinity ? "-Infinity" : n;
		};
		case t.STRING: return (t, n) => {
			if (typeof n != "string") throw kn(e, n);
			return n;
		};
		case t.BOOL: return (t, n) => {
			if (typeof n != "boolean") throw kn(e, n);
			return n;
		};
		case t.UINT64:
		case t.FIXED64:
		case t.INT64:
		case t.SFIXED64:
		case t.SINT64: return (t, n) => {
			if (typeof n == "bigint" || typeof n == "string" || typeof n == "number" && Number.isInteger(n)) return n.toString();
			throw kn(e, n);
		};
		case t.BYTES: return (t, n) => {
			if (n instanceof Uint8Array) return kt(n);
			throw kn(e, n);
		};
	}
}
function kn(e, t) {
	return /* @__PURE__ */ Error(`cannot encode ${e} to JSON: ${qe(e, t)?.message}`);
}
function An(e, t, n, r, i) {
	let a = r.$unknown;
	if (a === void 0) return;
	let o = /* @__PURE__ */ new Set();
	for (let s = 0; s < a.length; s++) {
		let { no: c } = a[s];
		if (!o.has(c)) {
			o.add(c);
			let a = n.getExtensionFor(i, c);
			if (!a) continue;
			let [s, l] = nn(a, en(r, a)), u = s[oe], d = yn(l)(t, u[l.localName]);
			d !== void 0 && (e[a.jsonName] = d);
		}
	}
}
function jn(e, t) {
	if (e.typeUrl === "") return {};
	let { registry: n } = t, r, i;
	if (n && (r = Qt(e, n), r && (i = n.getMessage(r.$typeName))), !i || !r) throw Error(`cannot encode message ${e.$typeName} to JSON: "${e.typeUrl}" is not in the type registry`);
	let a = be(i) ? { value: pn(i)(t, r) } : pn(i)(t, r);
	return a["@type"] = e.typeUrl, a;
}
function Mn(e) {
	let t = Number(e.seconds), n = e.nanos;
	if (t > 315576e6 || t < -315576e6) throw Error(`cannot encode message ${e.$typeName} to JSON: value out of range`);
	if (t > 0 && n < 0 || t < 0 && n > 0) throw Error(`cannot encode message ${e.$typeName} to JSON: nanos sign must match seconds sign`);
	let r = e.seconds.toString();
	if (n !== 0) {
		let e = Math.abs(n).toString();
		e = "0".repeat(9 - e.length) + e, e.substring(3) === "000000" ? e = e.substring(0, 3) : e.substring(6) === "000" && (e = e.substring(0, 6)), r += "." + e, n < 0 && t == 0 && (r = "-" + r);
	}
	return r + "s";
}
function Nn(e) {
	return e.paths.map((t) => {
		if (Pt(Nt(t)) !== t) throw Error(`cannot encode message ${e.$typeName} to JSON: lowerCamelCase of path name "${t}" is irreversible`);
		return Nt(t);
	}).join(",");
}
function Pn(e) {
	let t = {}, n = Object.keys(e.fields);
	for (let r = 0; r < n.length; r++) {
		let i = n[r];
		de(t, i, Fn(e.fields[i]));
	}
	return t;
}
function Fn(e) {
	switch (e.kind.case) {
		case "nullValue": return null;
		case "numberValue":
			if (!Number.isFinite(e.kind.value)) throw Error(`${e.$typeName} cannot be NaN or Infinity`);
			return e.kind.value;
		case "boolValue": return e.kind.value;
		case "stringValue": return e.kind.value;
		case "structValue": return Pn(e.kind.value);
		case "listValue": return In(e.kind.value);
		default: throw Error(`${e.$typeName} must have a value`);
	}
}
function In(e) {
	return e.values.map(Fn);
}
function Ln(e) {
	let t = Number(e.seconds) * 1e3;
	if (t < an || t > on) throw Error(`cannot encode message ${e.$typeName} to JSON: must be from 0001-01-01T00:00:00Z to 9999-12-31T23:59:59Z inclusive`);
	if (e.nanos < 0) throw Error(`cannot encode message ${e.$typeName} to JSON: nanos must not be negative`);
	if (e.nanos > 999999999) throw Error(`cannot encode message ${e.$typeName} to JSON: nanos must not be greater than 99999999`);
	let n = "Z";
	if (e.nanos > 0) {
		let t = (e.nanos + 1e9).toString().substring(1);
		n = t.substring(3) === "000000" ? "." + t.substring(0, 3) + "Z" : t.substring(6) === "000" ? "." + t.substring(0, 6) + "Z" : "." + t + "Z";
	}
	return new Date(t).toISOString().replace(".000Z", n);
}
//#endregion
//#region node_modules/@meshtastic/core/dist/chunk-DbKvDyjX.js
var Rn = Object.create, zn = Object.defineProperty, Bn = Object.getOwnPropertyDescriptor, Vn = Object.getOwnPropertyNames, Hn = Object.getPrototypeOf, Un = Object.prototype.hasOwnProperty, h = (e, t) => function() {
	return t || (0, e[Vn(e)[0]])((t = { exports: {} }).exports, t), t.exports;
}, Wn = (e) => {
	let t = {};
	for (var n in e) zn(t, n, {
		get: e[n],
		enumerable: !0
	});
	return t;
}, Gn = (e, t, n, r) => {
	if (t && typeof t == "object" || typeof t == "function") for (var i = Vn(t), a = 0, o = i.length, s; a < o; a++) s = i[a], !Un.call(e, s) && s !== n && zn(e, s, {
		get: ((e) => t[e]).bind(null, s),
		enumerable: !(r = Bn(t, s)) || r.enumerable
	});
	return e;
}, Kn = (e, t, n) => (n = e == null ? {} : Rn(Hn(e)), Gn(t || !e || !e.__esModule ? zn(n, "default", {
	value: e,
	enumerable: !0
}) : n, e));
//#endregion
//#region \0os
function qn() {
	return "localhost";
}
//#endregion
//#region \0path
function Jn(e) {
	return String(e || "");
}
//#endregion
//#region \0util
function Yn(e, ...t) {
	return t.map((e) => {
		if (typeof e == "object" && e) try {
			return JSON.stringify(e);
		} catch {
			return String(e);
		}
		return String(e);
	}).join(" ");
}
var Xn = { isNativeError: (e) => e instanceof Error }, Zn = [
	0,
	4129,
	8258,
	12387,
	16516,
	20645,
	24774,
	28903,
	33032,
	37161,
	41290,
	45419,
	49548,
	53677,
	57806,
	61935,
	4657,
	528,
	12915,
	8786,
	21173,
	17044,
	29431,
	25302,
	37689,
	33560,
	45947,
	41818,
	54205,
	50076,
	62463,
	58334,
	9314,
	13379,
	1056,
	5121,
	25830,
	29895,
	17572,
	21637,
	42346,
	46411,
	34088,
	38153,
	58862,
	62927,
	50604,
	54669,
	13907,
	9842,
	5649,
	1584,
	30423,
	26358,
	22165,
	18100,
	46939,
	42874,
	38681,
	34616,
	63455,
	59390,
	55197,
	51132,
	18628,
	22757,
	26758,
	30887,
	2112,
	6241,
	10242,
	14371,
	51660,
	55789,
	59790,
	63919,
	35144,
	39273,
	43274,
	47403,
	23285,
	19156,
	31415,
	27286,
	6769,
	2640,
	14899,
	10770,
	56317,
	52188,
	64447,
	60318,
	39801,
	35672,
	47931,
	43802,
	27814,
	31879,
	19684,
	23749,
	11298,
	15363,
	3168,
	7233,
	60846,
	64911,
	52716,
	56781,
	44330,
	48395,
	36200,
	40265,
	32407,
	28342,
	24277,
	20212,
	15891,
	11826,
	7761,
	3696,
	65439,
	61374,
	57309,
	53244,
	48923,
	44858,
	40793,
	36728,
	37256,
	33193,
	45514,
	41451,
	53516,
	49453,
	61774,
	57711,
	4224,
	161,
	12482,
	8419,
	20484,
	16421,
	28742,
	24679,
	33721,
	37784,
	41979,
	46042,
	49981,
	54044,
	58239,
	62302,
	689,
	4752,
	8947,
	13010,
	16949,
	21012,
	25207,
	29270,
	46570,
	42443,
	38312,
	34185,
	62830,
	58703,
	54572,
	50445,
	13538,
	9411,
	5280,
	1153,
	29798,
	25671,
	21540,
	17413,
	42971,
	47098,
	34713,
	38840,
	59231,
	63358,
	50973,
	55100,
	9939,
	14066,
	1681,
	5808,
	26199,
	30326,
	17941,
	22068,
	55628,
	51565,
	63758,
	59695,
	39368,
	35305,
	47498,
	43435,
	22596,
	18533,
	30726,
	26663,
	6336,
	2273,
	14466,
	10403,
	52093,
	56156,
	60223,
	64286,
	35833,
	39896,
	43963,
	48026,
	19061,
	23124,
	27191,
	31254,
	2801,
	6864,
	10931,
	14994,
	64814,
	60687,
	56684,
	52557,
	48554,
	44427,
	40424,
	36297,
	31782,
	27655,
	23652,
	19525,
	15522,
	11395,
	7392,
	3265,
	61215,
	65342,
	53085,
	57212,
	44955,
	49082,
	36825,
	40952,
	28183,
	32310,
	20053,
	24180,
	11923,
	16050,
	3793,
	7920
];
typeof Int32Array < "u" && (Zn = new Int32Array(Zn));
var Qn = (e, t) => {
	let n = t === void 0 ? 65535 : ~~t;
	for (let t = 0; t < e.length; t++) n = (Zn[(n >> 8 ^ e[t]) & 255] ^ n << 8) & 65535;
	return n;
}, $n = Object.defineProperty, g = (e) => {
	let t = {};
	for (var n in e) $n(t, n, {
		get: e[n],
		enumerable: !0
	});
	return t;
};
function er(e) {
	let t = !1, n = [];
	for (let r = 0; r < e.length; r++) {
		let i = e.charAt(r);
		switch (i) {
			case "_":
				t = !0;
				break;
			case "0":
			case "1":
			case "2":
			case "3":
			case "4":
			case "5":
			case "6":
			case "7":
			case "8":
			case "9":
				n.push(i), t = !1;
				break;
			default: t && (t = !1, i = i.toUpperCase()), n.push(i);
		}
	}
	return n.join("");
}
var tr = /* @__PURE__ */ new Set([
	"constructor",
	"toString",
	"toJSON",
	"valueOf"
]);
function nr(e) {
	return tr.has(e) ? e + "$" : e;
}
function rr() {
	let e = 0, t = 0;
	for (let n = 0; n < 28; n += 7) {
		let r = this.buf[this.pos++];
		if (e |= (r & 127) << n, !(r & 128)) return this.assertBounds(), [e, t];
	}
	let n = this.buf[this.pos++];
	if (e |= (n & 15) << 28, t = (n & 112) >> 4, !(n & 128)) return this.assertBounds(), [e, t];
	for (let n = 3; n <= 31; n += 7) {
		let r = this.buf[this.pos++];
		if (t |= (r & 127) << n, !(r & 128)) return this.assertBounds(), [e, t];
	}
	throw Error("invalid varint");
}
function ir(e, t, n) {
	for (let r = 0; r < 28; r += 7) {
		let i = e >>> r, a = !(!(i >>> 7) && t == 0), o = (a ? i | 128 : i) & 255;
		if (n.push(o), !a) return;
	}
	let r = e >>> 28 & 15 | (t & 7) << 4, i = !!(t >> 3);
	if (n.push((i ? r | 128 : r) & 255), i) {
		for (let e = 3; e < 31; e += 7) {
			let r = t >>> e, i = !!(r >>> 7), a = (i ? r | 128 : r) & 255;
			if (n.push(a), !i) return;
		}
		n.push(t >>> 31 & 1);
	}
}
var ar = 4294967296;
function or(e) {
	let t = e[0] === "-";
	t && (e = e.slice(1));
	let n = 1e6, r = 0, i = 0;
	function a(t, a) {
		let o = Number(e.slice(t, a));
		i *= n, r = r * n + o, r >= ar && (i += r / ar | 0, r %= ar);
	}
	return a(-24, -18), a(-18, -12), a(-12, -6), a(-6), t ? dr(r, i) : ur(r, i);
}
function sr(e, t) {
	let n = ur(e, t), r = n.hi & 2147483648;
	r && (n = dr(n.lo, n.hi));
	let i = cr(n.lo, n.hi);
	return r ? "-" + i : i;
}
function cr(e, t) {
	if ({lo: e, hi: t} = lr(e, t), t <= 2097151) return String(ar * t + e);
	let n = e & 16777215, r = (e >>> 24 | t << 8) & 16777215, i = t >> 16 & 65535, a = n + r * 6777216 + i * 6710656, o = r + i * 8147497, s = i * 2, c = 1e7;
	return a >= c && (o += Math.floor(a / c), a %= c), o >= c && (s += Math.floor(o / c), o %= c), s.toString() + fr(o) + fr(a);
}
function lr(e, t) {
	return {
		lo: e >>> 0,
		hi: t >>> 0
	};
}
function ur(e, t) {
	return {
		lo: e | 0,
		hi: t | 0
	};
}
function dr(e, t) {
	return t = ~t, e ? e = ~e + 1 : t += 1, ur(e, t);
}
var fr = (e) => {
	let t = String(e);
	return "0000000".slice(t.length) + t;
};
function pr(e, t) {
	if (e >= 0) {
		for (; e > 127;) t.push(e & 127 | 128), e >>>= 7;
		t.push(e);
	} else {
		for (let n = 0; n < 9; n++) t.push(e & 127 | 128), e >>= 7;
		t.push(1);
	}
}
function mr() {
	let e = this.buf[this.pos++], t = e & 127;
	if (!(e & 128) || (e = this.buf[this.pos++], t |= (e & 127) << 7, !(e & 128)) || (e = this.buf[this.pos++], t |= (e & 127) << 14, !(e & 128)) || (e = this.buf[this.pos++], t |= (e & 127) << 21, !(e & 128))) return this.assertBounds(), t;
	e = this.buf[this.pos++], t |= (e & 15) << 28;
	for (let t = 5; e & 128 && t < 10; t++) e = this.buf[this.pos++];
	if (e & 128) throw Error("invalid varint");
	return this.assertBounds(), t >>> 0;
}
var _ = /* @__PURE__ */ hr();
function hr() {
	let e = /* @__PURE__ */ new DataView(/* @__PURE__ */ new ArrayBuffer(8));
	if (typeof BigInt == "function" && typeof e.getBigInt64 == "function" && typeof e.getBigUint64 == "function" && typeof e.setBigInt64 == "function" && typeof e.setBigUint64 == "function" && (typeof process != "object" || {}.BUF_BIGINT_DISABLE !== "1")) {
		let t = BigInt("-9223372036854775808"), n = BigInt("9223372036854775807"), r = BigInt("0"), i = BigInt("18446744073709551615");
		return {
			zero: BigInt(0),
			supported: !0,
			parse(e) {
				let r = typeof e == "bigint" ? e : BigInt(e);
				if (r > n || r < t) throw Error(`invalid int64: ${e}`);
				return r;
			},
			uParse(e) {
				let t = typeof e == "bigint" ? e : BigInt(e);
				if (t > i || t < r) throw Error(`invalid uint64: ${e}`);
				return t;
			},
			enc(t) {
				return e.setBigInt64(0, this.parse(t), !0), {
					lo: e.getInt32(0, !0),
					hi: e.getInt32(4, !0)
				};
			},
			uEnc(t) {
				return e.setBigInt64(0, this.uParse(t), !0), {
					lo: e.getInt32(0, !0),
					hi: e.getInt32(4, !0)
				};
			},
			dec(t, n) {
				return e.setInt32(0, t, !0), e.setInt32(4, n, !0), e.getBigInt64(0, !0);
			},
			uDec(t, n) {
				return e.setInt32(0, t, !0), e.setInt32(4, n, !0), e.getBigUint64(0, !0);
			}
		};
	}
	return {
		zero: "0",
		supported: !1,
		parse(e) {
			return typeof e != "string" && (e = e.toString()), gr(e), e;
		},
		uParse(e) {
			return typeof e != "string" && (e = e.toString()), _r(e), e;
		},
		enc(e) {
			return typeof e != "string" && (e = e.toString()), gr(e), or(e);
		},
		uEnc(e) {
			return typeof e != "string" && (e = e.toString()), _r(e), or(e);
		},
		dec(e, t) {
			return sr(e, t);
		},
		uDec(e, t) {
			return cr(e, t);
		}
	};
}
function gr(e) {
	if (!/^-?[0-9]+$/.test(e)) throw Error("invalid int64: " + e);
}
function _r(e) {
	if (!/^[0-9]+$/.test(e)) throw Error("invalid uint64: " + e);
}
var v;
(function(e) {
	e[e.DOUBLE = 1] = "DOUBLE", e[e.FLOAT = 2] = "FLOAT", e[e.INT64 = 3] = "INT64", e[e.UINT64 = 4] = "UINT64", e[e.INT32 = 5] = "INT32", e[e.FIXED64 = 6] = "FIXED64", e[e.FIXED32 = 7] = "FIXED32", e[e.BOOL = 8] = "BOOL", e[e.STRING = 9] = "STRING", e[e.BYTES = 12] = "BYTES", e[e.UINT32 = 13] = "UINT32", e[e.SFIXED32 = 15] = "SFIXED32", e[e.SFIXED64 = 16] = "SFIXED64", e[e.SINT32 = 17] = "SINT32", e[e.SINT64 = 18] = "SINT64";
})(v ||= {});
function vr(e, t) {
	switch (e) {
		case v.STRING: return "";
		case v.BOOL: return !1;
		case v.DOUBLE:
		case v.FLOAT: return 0;
		case v.INT64:
		case v.UINT64:
		case v.SFIXED64:
		case v.FIXED64:
		case v.SINT64: return t ? "0" : _.zero;
		case v.BYTES: return /* @__PURE__ */ new Uint8Array();
		default: return 0;
	}
}
function yr(e, t) {
	switch (e) {
		case v.BOOL: return t === !1;
		case v.STRING: return t === "";
		case v.BYTES: return t instanceof Uint8Array && !t.byteLength;
		default: return t == 0;
	}
}
var br = 2, xr = Symbol.for("reflect unsafe local");
function Sr(e, t) {
	let n = e[t.localName].case;
	return n === void 0 ? n : t.fields.find((e) => e.localName === n);
}
function Cr(e, t) {
	let n = t.localName;
	if (t.oneof) return e[t.oneof.localName].case === n;
	if (t.presence != br) return e[n] !== void 0 && Object.prototype.hasOwnProperty.call(e, n);
	switch (t.fieldKind) {
		case "list": return e[n].length > 0;
		case "map": return Object.keys(e[n]).length > 0;
		case "scalar": return !yr(t.scalar, e[n]);
		case "enum": return e[n] !== t.enum.values[0].number;
	}
	throw Error("message field with implicit presence");
}
function wr(e, t) {
	return Object.prototype.hasOwnProperty.call(e, t) && e[t] !== void 0;
}
function Tr(e, t) {
	if (t.oneof) {
		let n = e[t.oneof.localName];
		return n.case === t.localName ? n.value : void 0;
	}
	return e[t.localName];
}
function Er(e, t, n) {
	t.oneof ? e[t.oneof.localName] = {
		case: t.localName,
		value: n
	} : e[t.localName] = n;
}
function Dr(e, t) {
	let n = t.localName;
	if (t.oneof) {
		let r = t.oneof.localName;
		e[r].case === n && (e[r] = { case: void 0 });
	} else if (t.presence != br) delete e[n];
	else switch (t.fieldKind) {
		case "map":
			e[n] = {};
			break;
		case "list":
			e[n] = [];
			break;
		case "enum":
			e[n] = t.enum.values[0].number;
			break;
		case "scalar": e[n] = vr(t.scalar, t.longAsString);
	}
}
function Or(e) {
	for (let t of e.field) wr(t, "jsonName") || (t.jsonName = er(t.name));
	e.nestedType.forEach(Or);
}
function kr(e, t) {
	let n = e.values.find((e) => e.name === t);
	if (!n) throw Error(`cannot parse ${e} default value: ${t}`);
	return n.number;
}
function Ar(e, t) {
	switch (e) {
		case v.STRING: return t;
		case v.BYTES: {
			let n = jr(t);
			if (n === !1) throw Error(`cannot parse ${v[e]} default value: ${t}`);
			return n;
		}
		case v.INT64:
		case v.SFIXED64:
		case v.SINT64: return _.parse(t);
		case v.UINT64:
		case v.FIXED64: return _.uParse(t);
		case v.DOUBLE:
		case v.FLOAT: switch (t) {
			case "inf": return Infinity;
			case "-inf": return -Infinity;
			case "nan": return NaN;
			default: return parseFloat(t);
		}
		case v.BOOL: return t === "true";
		case v.INT32:
		case v.UINT32:
		case v.SINT32:
		case v.FIXED32:
		case v.SFIXED32: return parseInt(t, 10);
	}
}
function jr(e) {
	let t = [], n = {
		tail: e,
		c: "",
		next() {
			return this.tail.length != 0 && (this.c = this.tail[0], this.tail = this.tail.substring(1), !0);
		},
		take(e) {
			if (this.tail.length >= e) {
				let t = this.tail.substring(0, e);
				return this.tail = this.tail.substring(e), t;
			}
			return !1;
		}
	};
	for (; n.next();) switch (n.c) {
		case "\\":
			if (n.next()) switch (n.c) {
				case "\\":
					t.push(n.c.charCodeAt(0));
					break;
				case "b":
					t.push(8);
					break;
				case "f":
					t.push(12);
					break;
				case "n":
					t.push(10);
					break;
				case "r":
					t.push(13);
					break;
				case "t":
					t.push(9);
					break;
				case "v":
					t.push(11);
					break;
				case "0":
				case "1":
				case "2":
				case "3":
				case "4":
				case "5":
				case "6":
				case "7": {
					let e = n.c, r = n.take(2);
					if (r === !1) return !1;
					let i = parseInt(e + r, 8);
					if (Number.isNaN(i)) return !1;
					t.push(i);
					break;
				}
				case "x": {
					let e = n.c, r = n.take(2);
					if (r === !1) return !1;
					let i = parseInt(e + r, 16);
					if (Number.isNaN(i)) return !1;
					t.push(i);
					break;
				}
				case "u": {
					let e = n.c, r = n.take(4);
					if (r === !1) return !1;
					let i = parseInt(e + r, 16);
					if (Number.isNaN(i)) return !1;
					let a = /* @__PURE__ */ new Uint8Array(4);
					new DataView(a.buffer).setInt32(0, i, !0), t.push(a[0], a[1], a[2], a[3]);
					break;
				}
				case "U": {
					let e = n.c, r = n.take(8);
					if (r === !1) return !1;
					let i = _.uEnc(e + r), a = /* @__PURE__ */ new Uint8Array(8), o = new DataView(a.buffer);
					o.setInt32(0, i.lo, !0), o.setInt32(4, i.hi, !0), t.push(a[0], a[1], a[2], a[3], a[4], a[5], a[6], a[7]);
					break;
				}
			}
			break;
		default: t.push(n.c.charCodeAt(0));
	}
	return new Uint8Array(t);
}
function* Mr(e) {
	switch (e.kind) {
		case "file":
			for (let t of e.messages) yield t, yield* Mr(t);
			yield* e.enums, yield* e.services, yield* e.extensions;
			break;
		case "message":
			for (let t of e.nestedMessages) yield t, yield* Mr(t);
			yield* e.nestedEnums, yield* e.nestedExtensions;
	}
}
function Nr(...e) {
	let t = Pr();
	if (!e.length) return t;
	if ("$typeName" in e[0] && e[0].$typeName == "google.protobuf.FileDescriptorSet") {
		for (let n of e[0].file) $r(n, t);
		return t;
	}
	if ("$typeName" in e[0]) {
		let n = e[0], r = e[1], i = /* @__PURE__ */ new Set();
		function a(e) {
			let n = [];
			for (let a of e.dependency) {
				if (t.getFile(a) != null || i.has(a)) continue;
				let o = r(a);
				if (!o) throw Error(`Unable to resolve ${a}, imported by ${e.name}`);
				"kind" in o ? t.addFile(o, !1, !0) : (i.add(o.name), n.push(o));
			}
			return n.concat(...n.map(a));
		}
		for (let e of [n, ...a(n)].reverse()) $r(e, t);
	} else for (let n of e) for (let e of n.files) t.addFile(e);
	return t;
}
function Pr() {
	let e = /* @__PURE__ */ new Map(), t = /* @__PURE__ */ new Map(), n = /* @__PURE__ */ new Map();
	return {
		kind: "registry",
		types: e,
		extendees: t,
		[Symbol.iterator]() {
			return e.values();
		},
		get files() {
			return n.values();
		},
		addFile(e, t, r) {
			if (n.set(e.proto.name, e), !t) for (let t of Mr(e)) this.add(t);
			if (r) for (let n of e.dependencies) this.addFile(n, t, r);
		},
		add(n) {
			if (n.kind == "extension") {
				let e = t.get(n.extendee.typeName);
				e || t.set(n.extendee.typeName, e = /* @__PURE__ */ new Map()), e.set(n.number, n);
			}
			e.set(n.typeName, n);
		},
		get(t) {
			return e.get(t);
		},
		getFile(e) {
			return n.get(e);
		},
		getMessage(t) {
			let n = e.get(t);
			return n?.kind == "message" ? n : void 0;
		},
		getEnum(t) {
			let n = e.get(t);
			return n?.kind == "enum" ? n : void 0;
		},
		getExtension(t) {
			let n = e.get(t);
			return n?.kind == "extension" ? n : void 0;
		},
		getExtensionFor(e, n) {
			return t.get(e.typeName)?.get(n);
		},
		getService(t) {
			let n = e.get(t);
			return n?.kind == "service" ? n : void 0;
		}
	};
}
var Fr = 998, Ir = 999, Lr = 9, Rr = 10, zr = 11, Br = 12, Vr = 14, Hr = 3, Ur = 2, Wr = 1, Gr = 0, Kr = 1, qr = 2, Jr = 3, Yr = 1, Xr = 2, Zr = 1, Qr = {
	998: {
		fieldPresence: 1,
		enumType: 2,
		repeatedFieldEncoding: 2,
		utf8Validation: 3,
		messageEncoding: 1,
		jsonFormat: 2,
		enforceNamingStyle: 2,
		defaultSymbolVisibility: 1
	},
	999: {
		fieldPresence: 2,
		enumType: 1,
		repeatedFieldEncoding: 1,
		utf8Validation: 2,
		messageEncoding: 1,
		jsonFormat: 1,
		enforceNamingStyle: 2,
		defaultSymbolVisibility: 1
	},
	1e3: {
		fieldPresence: 1,
		enumType: 1,
		repeatedFieldEncoding: 1,
		utf8Validation: 2,
		messageEncoding: 1,
		jsonFormat: 1,
		enforceNamingStyle: 2,
		defaultSymbolVisibility: 1
	},
	1001: {
		fieldPresence: 1,
		enumType: 1,
		repeatedFieldEncoding: 1,
		utf8Validation: 2,
		messageEncoding: 1,
		jsonFormat: 1,
		enforceNamingStyle: 1,
		defaultSymbolVisibility: 2
	}
};
function $r(e, t) {
	let n = {
		kind: "file",
		proto: e,
		deprecated: e.options?.deprecated ?? !1,
		edition: ci(e),
		name: e.name.replace(/\.proto$/, ""),
		dependencies: li(e, t),
		enums: [],
		messages: [],
		extensions: [],
		services: [],
		toString() {
			return `file ${e.name}`;
		}
	}, r = /* @__PURE__ */ new Map(), i = {
		get(e) {
			return r.get(e);
		},
		add(e) {
			y(e.proto.options?.mapEntry === !0), r.set(e.typeName, e);
		}
	};
	for (let r of e.enumType) ni(r, n, void 0, t);
	for (let r of e.messageType) ri(r, n, void 0, t, i);
	for (let r of e.service) ii(r, n, t);
	ei(n, t);
	for (let e of r.values()) ti(e, t, i);
	for (let e of n.messages) ti(e, t, i), ei(e, t);
	t.addFile(n, !0);
}
function ei(e, t) {
	switch (e.kind) {
		case "file":
			for (let n of e.proto.extension) {
				let r = si(n, e, t);
				e.extensions.push(r), t.add(r);
			}
			break;
		case "message":
			for (let n of e.proto.extension) {
				let r = si(n, e, t);
				e.nestedExtensions.push(r), t.add(r);
			}
			for (let n of e.nestedMessages) ei(n, t);
	}
}
function ti(e, t, n) {
	let r = e.proto.oneofDecl.map((t) => oi(t, e)), i = /* @__PURE__ */ new Set();
	for (let a of e.proto.field) {
		let o = mi(a, r), s = si(a, e, t, o, n);
		e.fields.push(s), e.field[s.localName] = s, o === void 0 ? e.members.push(s) : (o.fields.push(s), i.has(o) || (i.add(o), e.members.push(o)));
	}
	for (let t of r.filter((e) => i.has(e))) e.oneofs.push(t);
	for (let r of e.nestedMessages) ti(r, t, n);
}
function ni(e, t, n, r) {
	let i = ui(e.name, e.value), a = {
		kind: "enum",
		proto: e,
		deprecated: e.options?.deprecated ?? !1,
		file: t,
		parent: n,
		open: !0,
		name: e.name,
		typeName: fi(e, n, t),
		value: {},
		values: [],
		sharedPrefix: i,
		toString() {
			return `enum ${this.typeName}`;
		}
	};
	a.open = vi(a), r.add(a);
	for (let t of e.value) {
		let e = t.name;
		a.values.push(a.value[t.number] = {
			kind: "enum_value",
			proto: t,
			deprecated: t.options?.deprecated ?? !1,
			parent: a,
			name: e,
			localName: nr(i == null ? e : e.substring(i.length)),
			number: t.number,
			toString() {
				return `enum value ${a.typeName}.${e}`;
			}
		});
	}
	(n?.nestedEnums ?? t.enums).push(a);
}
function ri(e, t, n, r, i) {
	let a = {
		kind: "message",
		proto: e,
		deprecated: e.options?.deprecated ?? !1,
		file: t,
		parent: n,
		name: e.name,
		typeName: fi(e, n, t),
		fields: [],
		field: {},
		oneofs: [],
		members: [],
		nestedEnums: [],
		nestedMessages: [],
		nestedExtensions: [],
		toString() {
			return `message ${this.typeName}`;
		}
	};
	e.options?.mapEntry === !0 ? i.add(a) : ((n?.nestedMessages ?? t.messages).push(a), r.add(a));
	for (let n of e.enumType) ni(n, t, a, r);
	for (let n of e.nestedType) ri(n, t, a, r, i);
}
function ii(e, t, n) {
	let r = {
		kind: "service",
		proto: e,
		deprecated: e.options?.deprecated ?? !1,
		file: t,
		name: e.name,
		typeName: fi(e, void 0, t),
		methods: [],
		method: {},
		toString() {
			return `service ${this.typeName}`;
		}
	};
	t.services.push(r), n.add(r);
	for (let t of e.method) {
		let e = ai(t, r, n);
		r.methods.push(e), r.method[e.localName] = e;
	}
}
function ai(e, t, n) {
	let r;
	r = e.clientStreaming && e.serverStreaming ? "bidi_streaming" : e.clientStreaming ? "client_streaming" : e.serverStreaming ? "server_streaming" : "unary";
	let i = n.getMessage(pi(e.inputType)), a = n.getMessage(pi(e.outputType));
	y(i, `invalid MethodDescriptorProto: input_type ${e.inputType} not found`), y(a, `invalid MethodDescriptorProto: output_type ${e.inputType} not found`);
	let o = e.name;
	return {
		kind: "rpc",
		proto: e,
		deprecated: e.options?.deprecated ?? !1,
		parent: t,
		name: o,
		localName: nr(o.length ? nr(o[0].toLowerCase() + o.substring(1)) : o),
		methodKind: r,
		input: i,
		output: a,
		idempotency: e.options?.idempotencyLevel ?? Gr,
		toString() {
			return `rpc ${t.typeName}.${o}`;
		}
	};
}
function oi(e, t) {
	return {
		kind: "oneof",
		proto: e,
		deprecated: !1,
		parent: t,
		fields: [],
		name: e.name,
		localName: nr(er(e.name)),
		toString() {
			return `oneof ${t.typeName}.${this.name}`;
		}
	};
}
function si(e, t, n, r, i) {
	let a = i === void 0, o = {
		kind: "field",
		proto: e,
		deprecated: e.options?.deprecated ?? !1,
		name: e.name,
		number: e.number,
		scalar: void 0,
		message: void 0,
		enum: void 0,
		presence: hi(e, r, a, t),
		listKind: void 0,
		mapKind: void 0,
		mapKey: void 0,
		delimitedEncoding: void 0,
		packed: void 0,
		longAsString: !1,
		getDefaultValue: void 0
	};
	if (a) {
		let r = t.kind == "file" ? t : t.file, i = t.kind == "file" ? void 0 : t, a = fi(e, i, r);
		o.kind = "extension", o.file = r, o.parent = i, o.oneof = void 0, o.typeName = a, o.jsonName = `[${a}]`, o.toString = () => `extension ${a}`;
		let s = n.getMessage(pi(e.extendee));
		y(s, `invalid FieldDescriptorProto: extendee ${e.extendee} not found`), o.extendee = s;
	} else {
		let n = t;
		y(n.kind == "message"), o.parent = n, o.oneof = r, o.localName = r ? er(e.name) : nr(er(e.name)), o.jsonName = e.jsonName, o.toString = () => `field ${n.typeName}.${e.name}`;
	}
	let s = e.label, c = e.type, l = e.options?.jstype;
	if (s === Hr) {
		let r = c == zr ? i?.get(pi(e.typeName)) : void 0;
		if (r) {
			o.fieldKind = "map";
			let { key: e, value: t } = _i(r);
			return o.mapKey = e.scalar, o.mapKind = t.fieldKind, o.message = t.message, o.delimitedEncoding = !1, o.enum = t.enum, o.scalar = t.scalar, o;
		}
		switch (o.fieldKind = "list", c) {
			case zr:
			case Rr:
				o.listKind = "message", o.message = n.getMessage(pi(e.typeName)), y(o.message), o.delimitedEncoding = yi(e, t);
				break;
			case Vr:
				o.listKind = "enum", o.enum = n.getEnum(pi(e.typeName)), y(o.enum);
				break;
			default: o.listKind = "scalar", o.scalar = c, o.longAsString = l == Wr;
		}
		return o.packed = gi(e, t), o;
	}
	switch (c) {
		case zr:
		case Rr:
			o.fieldKind = "message", o.message = n.getMessage(pi(e.typeName)), y(o.message, `invalid FieldDescriptorProto: type_name ${e.typeName} not found`), o.delimitedEncoding = yi(e, t), o.getDefaultValue = () => void 0;
			break;
		case Vr: {
			let t = n.getEnum(pi(e.typeName));
			y(t !== void 0, `invalid FieldDescriptorProto: type_name ${e.typeName} not found`), o.fieldKind = "enum", o.enum = n.getEnum(pi(e.typeName)), o.getDefaultValue = () => wr(e, "defaultValue") ? kr(t, e.defaultValue) : void 0;
			break;
		}
		default: o.fieldKind = "scalar", o.scalar = c, o.longAsString = l == Wr, o.getDefaultValue = () => wr(e, "defaultValue") ? Ar(c, e.defaultValue) : void 0;
	}
	return o;
}
function ci(e) {
	switch (e.syntax) {
		case "":
		case "proto2": return Fr;
		case "proto3": return Ir;
		case "editions":
			if (e.edition in Qr) return e.edition;
			throw Error(`${e.name}: unsupported edition`);
		default: throw Error(`${e.name}: unsupported syntax "${e.syntax}"`);
	}
}
function li(e, t) {
	return e.dependency.map((n) => {
		let r = t.getFile(n);
		if (!r) throw Error(`Cannot find ${n}, imported by ${e.name}`);
		return r;
	});
}
function ui(e, t) {
	let n = di(e) + "_";
	for (let e of t) {
		if (!e.name.toLowerCase().startsWith(n)) return;
		let t = e.name.substring(n.length);
		if (t.length == 0 || /^\d/.test(t)) return;
	}
	return n;
}
function di(e) {
	return (e.substring(0, 1) + e.substring(1).replace(/[A-Z]/g, (e) => "_" + e)).toLowerCase();
}
function fi(e, t, n) {
	let r;
	return r = t ? `${t.typeName}.${e.name}` : n.proto.package.length > 0 ? `${n.proto.package}.${e.name}` : `${e.name}`, r;
}
function pi(e) {
	return e.startsWith(".") ? e.substring(1) : e;
}
function mi(e, t) {
	if (!wr(e, "oneofIndex") || e.proto3Optional) return;
	let n = t[e.oneofIndex];
	return y(n, `invalid FieldDescriptorProto: oneof #${e.oneofIndex} for field #${e.number} not found`), n;
}
function hi(e, t, n, r) {
	if (e.label == Ur) return Jr;
	if (e.label == Hr) return qr;
	if (t || e.proto3Optional || n) return Kr;
	let i = bi("fieldPresence", {
		proto: e,
		parent: r
	});
	return i == qr && (e.type == zr || e.type == Rr) ? Kr : i;
}
function gi(e, t) {
	if (e.label != Hr) return !1;
	switch (e.type) {
		case Lr:
		case Br:
		case Rr:
		case zr: return !1;
	}
	let n = e.options;
	return n && wr(n, "packed") ? n.packed : Yr == bi("repeatedFieldEncoding", {
		proto: e,
		parent: t
	});
}
function _i(e) {
	let t = e.fields.find((e) => e.number === 1), n = e.fields.find((e) => e.number === 2);
	return y(t && t.fieldKind == "scalar" && t.scalar != v.BYTES && t.scalar != v.FLOAT && t.scalar != v.DOUBLE && n && n.fieldKind != "list" && n.fieldKind != "map"), {
		key: t,
		value: n
	};
}
function vi(e) {
	return Zr == bi("enumType", {
		proto: e.proto,
		parent: e.parent ?? e.file
	});
}
function yi(e, t) {
	return e.type == Rr || Xr == bi("messageEncoding", {
		proto: e,
		parent: t
	});
}
function bi(e, t) {
	let n = t.proto.options?.features;
	if (n) {
		let t = n[e];
		if (t != 0) return t;
	}
	if ("kind" in t) {
		if (t.kind == "message") return bi(e, t.parent ?? t.file);
		let n = Qr[t.edition];
		if (!n) throw Error(`feature default for edition ${t.edition} not found`);
		return n[e];
	}
	return bi(e, t.parent);
}
function y(e, t) {
	if (!e) throw Error(t);
}
function xi(e) {
	let t = Si(e);
	return t.messageType.forEach(Or), Nr(t, () => void 0).getFile(t.name);
}
function Si(e) {
	return Object.assign(Object.create({
		syntax: "",
		edition: 0
	}), Object.assign(Object.assign({
		$typeName: "google.protobuf.FileDescriptorProto",
		dependency: [],
		publicDependency: [],
		weakDependency: [],
		optionDependency: [],
		service: [],
		extension: []
	}, e), {
		messageType: e.messageType.map(Ci),
		enumType: e.enumType.map(Ei)
	}));
}
function Ci(e) {
	return Object.assign(Object.create({ visibility: 0 }), {
		$typeName: "google.protobuf.DescriptorProto",
		name: e.name,
		field: e.field?.map(wi) ?? [],
		extension: [],
		nestedType: e.nestedType?.map(Ci) ?? [],
		enumType: e.enumType?.map(Ei) ?? [],
		extensionRange: e.extensionRange?.map((e) => Object.assign({ $typeName: "google.protobuf.DescriptorProto.ExtensionRange" }, e)) ?? [],
		oneofDecl: [],
		reservedRange: [],
		reservedName: []
	});
}
function wi(e) {
	return Object.assign(Object.create({
		label: 1,
		typeName: "",
		extendee: "",
		defaultValue: "",
		oneofIndex: 0,
		jsonName: "",
		proto3Optional: !1
	}), Object.assign(Object.assign({ $typeName: "google.protobuf.FieldDescriptorProto" }, e), { options: e.options ? Ti(e.options) : void 0 }));
}
function Ti(e) {
	return Object.assign(Object.create({
		ctype: 0,
		packed: !1,
		jstype: 0,
		lazy: !1,
		unverifiedLazy: !1,
		deprecated: !1,
		weak: !1,
		debugRedact: !1,
		retention: 0
	}), Object.assign(Object.assign({ $typeName: "google.protobuf.FieldOptions" }, e), {
		targets: e.targets ?? [],
		editionDefaults: e.editionDefaults?.map((e) => Object.assign({ $typeName: "google.protobuf.FieldOptions.EditionDefault" }, e)) ?? [],
		uninterpretedOption: []
	}));
}
function Ei(e) {
	return Object.assign(Object.create({ visibility: 0 }), {
		$typeName: "google.protobuf.EnumDescriptorProto",
		name: e.name,
		reservedName: [],
		reservedRange: [],
		value: e.value.map((e) => Object.assign({ $typeName: "google.protobuf.EnumValueDescriptorProto" }, e))
	});
}
function Di(e) {
	let t = Mi(), n = e.length * 3 / 4;
	e[e.length - 2] == "=" ? n -= 2 : e[e.length - 1] == "=" && --n;
	let r = new Uint8Array(n), i = 0, a = 0, o, s = 0;
	for (let n = 0; n < e.length; n++) {
		if (o = t[e.charCodeAt(n)], o === void 0) switch (e[n]) {
			case "=": a = 0;
			case "\n":
			case "\r":
			case "	":
			case " ": continue;
			default: throw Error("invalid base64 string");
		}
		switch (a) {
			case 0:
				s = o, a = 1;
				break;
			case 1:
				r[i++] = s << 2 | (o & 48) >> 4, s = o, a = 2;
				break;
			case 2:
				r[i++] = (s & 15) << 4 | (o & 60) >> 2, s = o, a = 3;
				break;
			case 3: r[i++] = (s & 3) << 6 | o, a = 0;
		}
	}
	if (a == 1) throw Error("invalid base64 string");
	return r.subarray(0, i);
}
var Oi, ki, Ai;
function ji(e) {
	return Oi || (Oi = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/".split(""), ki = Oi.slice(0, -2).concat("-", "_")), e == "url" ? ki : Oi;
}
function Mi() {
	if (!Ai) {
		Ai = [];
		let e = ji("std");
		for (let t = 0; t < e.length; t++) Ai[e[t].charCodeAt(0)] = t;
		Ai[45] = e.indexOf("+"), Ai[95] = e.indexOf("/");
	}
	return Ai;
}
function Ni(e, t) {
	return typeof e == "object" && e && "$typeName" in e && typeof e.$typeName == "string" ? t === void 0 || t.typeName === e.$typeName : !1;
}
var Pi = class extends Error {
	constructor(e, t, n = "FieldValueInvalidError") {
		super(t), this.name = n, this.field = () => e;
	}
};
function Fi(e) {
	return typeof e == "object" && !!e && !Array.isArray(e);
}
function Ii(e, t) {
	if (Fi(e) && xr in e && "add" in e && "field" in e && typeof e.field == "function") {
		if (t !== void 0) {
			let n = t, r = e.field();
			return n.listKind == r.listKind && n.scalar === r.scalar && n.message?.typeName === r.message?.typeName && n.enum?.typeName === r.enum?.typeName;
		}
		return !0;
	}
	return !1;
}
function Li(e, t) {
	if (Fi(e) && xr in e && "has" in e && "field" in e && typeof e.field == "function") {
		if (t !== void 0) {
			let n = t, r = e.field();
			return n.mapKey === r.mapKey && n.mapKind == r.mapKind && n.scalar === r.scalar && n.message?.typeName === r.message?.typeName && n.enum?.typeName === r.enum?.typeName;
		}
		return !0;
	}
	return !1;
}
function Ri(e, t) {
	return Fi(e) && xr in e && "desc" in e && Fi(e.desc) && e.desc.kind === "message" && (t === void 0 || e.desc.typeName == t.typeName);
}
var zi = Symbol.for("@bufbuild/protobuf/text-encoding");
function Bi() {
	if (globalThis[zi] == null) {
		let e = new globalThis.TextEncoder(), t = new globalThis.TextDecoder();
		globalThis[zi] = {
			encodeUtf8(t) {
				return e.encode(t);
			},
			decodeUtf8(e) {
				return t.decode(e);
			},
			checkUtf8(e) {
				try {
					return !0;
				} catch {
					return !1;
				}
			}
		};
	}
	return globalThis[zi];
}
var b;
(function(e) {
	e[e.Varint = 0] = "Varint", e[e.Bit64 = 1] = "Bit64", e[e.LengthDelimited = 2] = "LengthDelimited", e[e.StartGroup = 3] = "StartGroup", e[e.EndGroup = 4] = "EndGroup", e[e.Bit32 = 5] = "Bit32";
})(b ||= {});
var Vi = 34028234663852886e22, Hi = -34028234663852886e22, Ui = 4294967295, Wi = 2147483647, Gi = -2147483648, Ki = class {
	constructor(e = Bi().encodeUtf8) {
		this.encodeUtf8 = e, this.stack = [], this.chunks = [], this.buf = [];
	}
	finish() {
		this.buf.length && (this.chunks.push(new Uint8Array(this.buf)), this.buf = []);
		let e = 0;
		for (let t = 0; t < this.chunks.length; t++) e += this.chunks[t].length;
		let t = new Uint8Array(e), n = 0;
		for (let e = 0; e < this.chunks.length; e++) t.set(this.chunks[e], n), n += this.chunks[e].length;
		return this.chunks = [], t;
	}
	fork() {
		return this.stack.push({
			chunks: this.chunks,
			buf: this.buf
		}), this.chunks = [], this.buf = [], this;
	}
	join() {
		let e = this.finish(), t = this.stack.pop();
		if (!t) throw Error("invalid state, fork stack empty");
		return this.chunks = t.chunks, this.buf = t.buf, this.uint32(e.byteLength), this.raw(e);
	}
	tag(e, t) {
		return this.uint32((e << 3 | t) >>> 0);
	}
	raw(e) {
		return this.buf.length && (this.chunks.push(new Uint8Array(this.buf)), this.buf = []), this.chunks.push(e), this;
	}
	uint32(e) {
		for (Yi(e); e > 127;) this.buf.push(e & 127 | 128), e >>>= 7;
		return this.buf.push(e), this;
	}
	int32(e) {
		return Ji(e), pr(e, this.buf), this;
	}
	bool(e) {
		return this.buf.push(+!!e), this;
	}
	bytes(e) {
		return this.uint32(e.byteLength), this.raw(e);
	}
	string(e) {
		let t = this.encodeUtf8(e);
		return this.uint32(t.byteLength), this.raw(t);
	}
	float(e) {
		Xi(e);
		let t = /* @__PURE__ */ new Uint8Array(4);
		return new DataView(t.buffer).setFloat32(0, e, !0), this.raw(t);
	}
	double(e) {
		let t = /* @__PURE__ */ new Uint8Array(8);
		return new DataView(t.buffer).setFloat64(0, e, !0), this.raw(t);
	}
	fixed32(e) {
		Yi(e);
		let t = /* @__PURE__ */ new Uint8Array(4);
		return new DataView(t.buffer).setUint32(0, e, !0), this.raw(t);
	}
	sfixed32(e) {
		Ji(e);
		let t = /* @__PURE__ */ new Uint8Array(4);
		return new DataView(t.buffer).setInt32(0, e, !0), this.raw(t);
	}
	sint32(e) {
		return Ji(e), e = (e << 1 ^ e >> 31) >>> 0, pr(e, this.buf), this;
	}
	sfixed64(e) {
		let t = /* @__PURE__ */ new Uint8Array(8), n = new DataView(t.buffer), r = _.enc(e);
		return n.setInt32(0, r.lo, !0), n.setInt32(4, r.hi, !0), this.raw(t);
	}
	fixed64(e) {
		let t = /* @__PURE__ */ new Uint8Array(8), n = new DataView(t.buffer), r = _.uEnc(e);
		return n.setInt32(0, r.lo, !0), n.setInt32(4, r.hi, !0), this.raw(t);
	}
	int64(e) {
		let t = _.enc(e);
		return ir(t.lo, t.hi, this.buf), this;
	}
	sint64(e) {
		let t = _.enc(e), n = t.hi >> 31;
		return ir(t.lo << 1 ^ n, (t.hi << 1 | t.lo >>> 31) ^ n, this.buf), this;
	}
	uint64(e) {
		let t = _.uEnc(e);
		return ir(t.lo, t.hi, this.buf), this;
	}
}, qi = class {
	constructor(e, t = Bi().decodeUtf8) {
		this.decodeUtf8 = t, this.varint64 = rr, this.uint32 = mr, this.buf = e, this.len = e.length, this.pos = 0, this.view = new DataView(e.buffer, e.byteOffset, e.byteLength);
	}
	tag() {
		let e = this.uint32(), t = e >>> 3, n = e & 7;
		if (t <= 0 || n < 0 || n > 5) throw Error("illegal tag: field no " + t + " wire type " + n);
		return [t, n];
	}
	skip(e, t) {
		let n = this.pos;
		switch (e) {
			case b.Varint:
				for (; this.buf[this.pos++] & 128;);
				break;
			case b.Bit64: this.pos += 4;
			case b.Bit32:
				this.pos += 4;
				break;
			case b.LengthDelimited:
				let n = this.uint32();
				this.pos += n;
				break;
			case b.StartGroup:
				for (;;) {
					let [e, n] = this.tag();
					if (n === b.EndGroup) {
						if (t !== void 0 && e !== t) throw Error("invalid end group tag");
						break;
					}
					this.skip(n, e);
				}
				break;
			default: throw Error("cant skip wire type " + e);
		}
		return this.assertBounds(), this.buf.subarray(n, this.pos);
	}
	assertBounds() {
		if (this.pos > this.len) throw RangeError("premature EOF");
	}
	int32() {
		return this.uint32() | 0;
	}
	sint32() {
		let e = this.uint32();
		return e >>> 1 ^ -(e & 1);
	}
	int64() {
		return _.dec(...this.varint64());
	}
	uint64() {
		return _.uDec(...this.varint64());
	}
	sint64() {
		let [e, t] = this.varint64(), n = -(e & 1);
		return e = (e >>> 1 | (t & 1) << 31) ^ n, t = t >>> 1 ^ n, _.dec(e, t);
	}
	bool() {
		let [e, t] = this.varint64();
		return e !== 0 || t !== 0;
	}
	fixed32() {
		return this.view.getUint32((this.pos += 4) - 4, !0);
	}
	sfixed32() {
		return this.view.getInt32((this.pos += 4) - 4, !0);
	}
	fixed64() {
		return _.uDec(this.sfixed32(), this.sfixed32());
	}
	sfixed64() {
		return _.dec(this.sfixed32(), this.sfixed32());
	}
	float() {
		return this.view.getFloat32((this.pos += 4) - 4, !0);
	}
	double() {
		return this.view.getFloat64((this.pos += 8) - 8, !0);
	}
	bytes() {
		let e = this.uint32(), t = this.pos;
		return this.pos += e, this.assertBounds(), this.buf.subarray(t, t + e);
	}
	string() {
		return this.decodeUtf8(this.bytes());
	}
};
function Ji(e) {
	if (typeof e == "string") e = Number(e);
	else if (typeof e != "number") throw Error("invalid int32: " + typeof e);
	if (!Number.isInteger(e) || e > Wi || e < Gi) throw Error("invalid int32: " + e);
}
function Yi(e) {
	if (typeof e == "string") e = Number(e);
	else if (typeof e != "number") throw Error("invalid uint32: " + typeof e);
	if (!Number.isInteger(e) || e > Ui || e < 0) throw Error("invalid uint32: " + e);
}
function Xi(e) {
	if (typeof e == "string") {
		let t = e;
		if (e = Number(e), Number.isNaN(e) && t !== "NaN") throw Error("invalid float32: " + t);
	} else if (typeof e != "number") throw Error("invalid float32: " + typeof e);
	if (Number.isFinite(e) && (e > Vi || e < Hi)) throw Error("invalid float32: " + e);
}
function Zi(e, t) {
	let n = e.fieldKind == "list" ? Ii(t, e) : e.fieldKind == "map" ? Li(t, e) : ea(e, t);
	if (n === !0) return;
	let r;
	switch (e.fieldKind) {
		case "list":
			r = `expected ${aa(e)}, got ${ra(t)}`;
			break;
		case "map":
			r = `expected ${oa(e)}, got ${ra(t)}`;
			break;
		default: r = na(e, t, n);
	}
	return new Pi(e, r);
}
function Qi(e, t, n) {
	let r = ea(e, n);
	if (r !== !0) return new Pi(e, `list item #${t + 1}: ${na(e, n, r)}`);
}
function $i(e, t, n) {
	let r = ta(t, e.mapKey);
	if (r !== !0) return new Pi(e, `invalid map key: ${na({ scalar: e.mapKey }, t, r)}`);
	let i = ea(e, n);
	if (i !== !0) return new Pi(e, `map entry ${ra(t)}: ${na(e, n, i)}`);
}
function ea(e, t) {
	return e.scalar === void 0 ? e.enum === void 0 ? Ri(t, e.message) : e.enum.open ? Number.isInteger(t) : e.enum.values.some((e) => e.number === t) : ta(t, e.scalar);
}
function ta(e, t) {
	switch (t) {
		case v.DOUBLE: return typeof e == "number";
		case v.FLOAT: return typeof e == "number" ? Number.isNaN(e) || !Number.isFinite(e) ? !0 : e > Vi || e < Hi ? `${e.toFixed()} out of range` : !0 : !1;
		case v.INT32:
		case v.SFIXED32:
		case v.SINT32: return typeof e != "number" || !Number.isInteger(e) ? !1 : e > Wi || e < Gi ? `${e.toFixed()} out of range` : !0;
		case v.FIXED32:
		case v.UINT32: return typeof e != "number" || !Number.isInteger(e) ? !1 : e > Ui || e < 0 ? `${e.toFixed()} out of range` : !0;
		case v.BOOL: return typeof e == "boolean";
		case v.STRING: return typeof e == "string" ? Bi().checkUtf8(e) || "invalid UTF8" : !1;
		case v.BYTES: return e instanceof Uint8Array;
		case v.INT64:
		case v.SFIXED64:
		case v.SINT64:
			if (typeof e == "bigint" || typeof e == "number" || typeof e == "string" && e.length > 0) try {
				return _.parse(e), !0;
			} catch {
				return `${e} out of range`;
			}
			return !1;
		case v.FIXED64:
		case v.UINT64:
			if (typeof e == "bigint" || typeof e == "number" || typeof e == "string" && e.length > 0) try {
				return _.uParse(e), !0;
			} catch {
				return `${e} out of range`;
			}
			return !1;
	}
}
function na(e, t, n) {
	return n = typeof n == "string" ? `: ${n}` : `, got ${ra(t)}`, e.scalar === void 0 ? e.enum === void 0 ? `expected ${ia(e.message)}` + n : `expected ${e.enum.toString()}` + n : `expected ${sa(e.scalar)}` + n;
}
function ra(e) {
	switch (typeof e) {
		case "object": return e === null ? "null" : e instanceof Uint8Array ? `Uint8Array(${e.length})` : Array.isArray(e) ? `Array(${e.length})` : Ii(e) ? aa(e.field()) : Li(e) ? oa(e.field()) : Ri(e) ? ia(e.desc) : Ni(e) ? `message ${e.$typeName}` : "object";
		case "string": return e.length > 30 ? "string" : `"${e.split("\"").join("\\\"")}"`;
		case "boolean": return String(e);
		case "number": return String(e);
		case "bigint": return String(e) + "n";
		default: return typeof e;
	}
}
function ia(e) {
	return `ReflectMessage (${e.typeName})`;
}
function aa(e) {
	switch (e.listKind) {
		case "message": return `ReflectList (${e.message.toString()})`;
		case "enum": return `ReflectList (${e.enum.toString()})`;
		case "scalar": return `ReflectList (${v[e.scalar]})`;
	}
}
function oa(e) {
	switch (e.mapKind) {
		case "message": return `ReflectMap (${v[e.mapKey]}, ${e.message.toString()})`;
		case "enum": return `ReflectMap (${v[e.mapKey]}, ${e.enum.toString()})`;
		case "scalar": return `ReflectMap (${v[e.mapKey]}, ${v[e.scalar]})`;
	}
}
function sa(e) {
	switch (e) {
		case v.STRING: return "string";
		case v.BOOL: return "boolean";
		case v.INT64:
		case v.SINT64:
		case v.SFIXED64: return "bigint (int64)";
		case v.UINT64:
		case v.FIXED64: return "bigint (uint64)";
		case v.BYTES: return "Uint8Array";
		case v.DOUBLE: return "number (float64)";
		case v.FLOAT: return "number (float32)";
		case v.FIXED32:
		case v.UINT32: return "number (uint32)";
		case v.INT32:
		case v.SFIXED32:
		case v.SINT32: return "number (int32)";
	}
}
function ca(e) {
	return ua(e.$typeName);
}
function la(e) {
	let t = e.fields[0];
	return ua(e.typeName) && t !== void 0 && t.fieldKind == "scalar" && t.name == "value" && t.number == 1;
}
function ua(e) {
	return e.startsWith("google.protobuf.") && [
		"DoubleValue",
		"FloatValue",
		"Int64Value",
		"UInt64Value",
		"Int32Value",
		"UInt32Value",
		"BoolValue",
		"StringValue",
		"BytesValue"
	].includes(e.substring(16));
}
var da = 999, fa = 998, pa = 2;
function x(e, t) {
	if (Ni(t, e)) return t;
	let n = Ca(e);
	return t !== void 0 && ma(e, n, t), n;
}
function ma(e, t, n) {
	for (let r of e.members) {
		let e = n[r.localName];
		if (e == null) continue;
		let i;
		if (r.kind == "oneof") {
			let t = Sr(n, r);
			if (!t) continue;
			i = t, e = Tr(n, t);
		} else i = r;
		switch (i.fieldKind) {
			case "message":
				e = va(i, e);
				break;
			case "scalar":
				e = ha(i, e);
				break;
			case "list":
				e = _a(i, e);
				break;
			case "map": e = ga(i, e);
		}
		Er(t, i, e);
	}
	return t;
}
function ha(e, t) {
	return e.scalar == v.BYTES ? ya(t) : t;
}
function ga(e, t) {
	if (Fi(t)) {
		if (e.scalar == v.BYTES) return ba(t, ya);
		if (e.mapKind == "message") return ba(t, (t) => va(e, t));
	}
	return t;
}
function _a(e, t) {
	if (Array.isArray(t)) {
		if (e.scalar == v.BYTES) return t.map(ya);
		if (e.listKind == "message") return t.map((t) => va(e, t));
	}
	return t;
}
function va(e, t) {
	if (e.fieldKind == "message" && !e.oneof && la(e.message)) return ha(e.message.fields[0], t);
	if (Fi(t)) {
		if (e.message.typeName == "google.protobuf.Struct" && e.parent.typeName !== "google.protobuf.Value") return t;
		if (!Ni(t, e.message)) return x(e.message, t);
	}
	return t;
}
function ya(e) {
	return Array.isArray(e) ? new Uint8Array(e) : e;
}
function ba(e, t) {
	let n = {};
	for (let r of Object.entries(e)) n[r[0]] = t(r[1]);
	return n;
}
var xa = Symbol(), Sa = /* @__PURE__ */ new WeakMap();
function Ca(e) {
	let t;
	if (wa(e)) {
		let n = Sa.get(e), r, i;
		if (n) ({prototype: r, members: i} = n);
		else {
			r = {}, i = /* @__PURE__ */ new Set();
			for (let t of e.members) t.kind != "oneof" && (t.fieldKind == "scalar" || t.fieldKind == "enum") && t.presence != pa && (i.add(t), r[t.localName] = Ta(t));
			Sa.set(e, {
				prototype: r,
				members: i
			});
		}
		t = Object.create(r), t.$typeName = e.typeName;
		for (let n of e.members) i.has(n) || (n.kind != "field" || n.fieldKind != "message" && (n.fieldKind != "scalar" && n.fieldKind != "enum" || n.presence == pa)) && (t[n.localName] = Ta(n));
	} else {
		t = { $typeName: e.typeName };
		for (let n of e.members) (n.kind == "oneof" || n.presence == pa) && (t[n.localName] = Ta(n));
	}
	return t;
}
function wa(e) {
	switch (e.file.edition) {
		case da: return !1;
		case fa: return !0;
		default: return e.fields.some((e) => e.presence != pa && e.fieldKind != "message" && !e.oneof);
	}
}
function Ta(e) {
	if (e.kind == "oneof") return { case: void 0 };
	if (e.fieldKind == "list") return [];
	if (e.fieldKind == "map") return {};
	if (e.fieldKind == "message") return xa;
	let t = e.getDefaultValue();
	return t === void 0 ? e.fieldKind == "scalar" ? vr(e.scalar, e.longAsString) : e.enum.values[0].number : e.fieldKind == "scalar" && e.longAsString ? t.toString() : t;
}
function Ea(e, t, n = !0) {
	return new Da(e, t, n);
}
var Da = class {
	get sortedFields() {
		return this._sortedFields ??= this.desc.fields.concat().sort((e, t) => e.number - t.number);
	}
	constructor(e, t, n = !0) {
		this.lists = /* @__PURE__ */ new Map(), this.maps = /* @__PURE__ */ new Map(), this.check = n, this.desc = e, this.message = this[xr] = t ?? x(e), this.fields = e.fields, this.oneofs = e.oneofs, this.members = e.members;
	}
	findNumber(e) {
		return this._fieldsByNumber ||= new Map(this.desc.fields.map((e) => [e.number, e])), this._fieldsByNumber.get(e);
	}
	oneofCase(e) {
		return Oa(this.message, e), Sr(this.message, e);
	}
	isSet(e) {
		return Oa(this.message, e), Cr(this.message, e);
	}
	clear(e) {
		Oa(this.message, e), Dr(this.message, e);
	}
	get(e) {
		Oa(this.message, e);
		let t = Tr(this.message, e);
		switch (e.fieldKind) {
			case "list":
				let n = this.lists.get(e);
				return (!n || n[xr] !== t) && this.lists.set(e, n = new ka(e, t, this.check)), n;
			case "map":
				let r = this.maps.get(e);
				return (!r || r[xr] !== t) && this.maps.set(e, r = new Aa(e, t, this.check)), r;
			case "message": return Ma(e, t, this.check);
			case "scalar": return t === void 0 ? vr(e.scalar, !1) : za(e, t);
			case "enum": return t ?? e.enum.values[0].number;
		}
	}
	set(e, t) {
		if (Oa(this.message, e), this.check) {
			let n = Zi(e, t);
			if (n) throw n;
		}
		let n;
		n = e.fieldKind == "message" ? ja(e, t) : Li(t) || Ii(t) ? t[xr] : Ba(e, t), Er(this.message, e, n);
	}
	getUnknown() {
		return this.message.$unknown;
	}
	setUnknown(e) {
		this.message.$unknown = e;
	}
};
function Oa(e, t) {
	if (t.parent.typeName !== e.$typeName) throw new Pi(t, `cannot use ${t.toString()} with message ${e.$typeName}`, "ForeignFieldError");
}
var ka = class {
	field() {
		return this._field;
	}
	get size() {
		return this._arr.length;
	}
	constructor(e, t, n) {
		this._field = e, this._arr = this[xr] = t, this.check = n;
	}
	get(e) {
		let t = this._arr[e];
		return t === void 0 ? void 0 : Pa(this._field, t, this.check);
	}
	set(e, t) {
		if (e < 0 || e >= this._arr.length) throw new Pi(this._field, `list item #${e + 1}: out of range`);
		if (this.check) {
			let n = Qi(this._field, e, t);
			if (n) throw n;
		}
		this._arr[e] = Na(this._field, t);
	}
	add(e) {
		if (this.check) {
			let t = Qi(this._field, this._arr.length, e);
			if (t) throw t;
		}
		this._arr.push(Na(this._field, e));
	}
	clear() {
		this._arr.splice(0, this._arr.length);
	}
	[Symbol.iterator]() {
		return this.values();
	}
	keys() {
		return this._arr.keys();
	}
	*values() {
		for (let e of this._arr) yield Pa(this._field, e, this.check);
	}
	*entries() {
		for (let e = 0; e < this._arr.length; e++) yield [e, Pa(this._field, this._arr[e], this.check)];
	}
}, Aa = class {
	constructor(e, t, n = !0) {
		this.obj = this[xr] = t ?? {}, this.check = n, this._field = e;
	}
	field() {
		return this._field;
	}
	set(e, t) {
		if (this.check) {
			let n = $i(this._field, e, t);
			if (n) throw n;
		}
		return this.obj[La(e)] = Fa(this._field, t), this;
	}
	delete(e) {
		let t = La(e), n = Object.prototype.hasOwnProperty.call(this.obj, t);
		return n && delete this.obj[t], n;
	}
	clear() {
		for (let e of Object.keys(this.obj)) delete this.obj[e];
	}
	get(e) {
		let t = this.obj[La(e)];
		return t !== void 0 && (t = Ia(this._field, t, this.check)), t;
	}
	has(e) {
		return Object.prototype.hasOwnProperty.call(this.obj, La(e));
	}
	*keys() {
		for (let e of Object.keys(this.obj)) yield Ra(e, this._field.mapKey);
	}
	*entries() {
		for (let e of Object.entries(this.obj)) yield [Ra(e[0], this._field.mapKey), Ia(this._field, e[1], this.check)];
	}
	[Symbol.iterator]() {
		return this.entries();
	}
	get size() {
		return Object.keys(this.obj).length;
	}
	*values() {
		for (let e of Object.values(this.obj)) yield Ia(this._field, e, this.check);
	}
	forEach(e, t) {
		for (let n of this.entries()) e.call(t, n[1], n[0], this);
	}
};
function ja(e, t) {
	return Ri(t) ? ca(t.message) && !e.oneof && e.fieldKind == "message" ? t.message.value : t.desc.typeName == "google.protobuf.Struct" && e.parent.typeName != "google.protobuf.Value" ? Ha(t.message) : t.message : t;
}
function Ma(e, t, n) {
	return t !== void 0 && (la(e.message) && !e.oneof && e.fieldKind == "message" ? t = {
		$typeName: e.message.typeName,
		value: za(e.message.fields[0], t)
	} : e.message.typeName == "google.protobuf.Struct" && e.parent.typeName != "google.protobuf.Value" && Fi(t) && (t = Va(t))), new Da(e.message, t, n);
}
function Na(e, t) {
	return e.listKind == "message" ? ja(e, t) : Ba(e, t);
}
function Pa(e, t, n) {
	return e.listKind == "message" ? Ma(e, t, n) : za(e, t);
}
function Fa(e, t) {
	return e.mapKind == "message" ? ja(e, t) : Ba(e, t);
}
function Ia(e, t, n) {
	return e.mapKind == "message" ? Ma(e, t, n) : t;
}
function La(e) {
	return typeof e == "string" || typeof e == "number" ? e : String(e);
}
function Ra(e, t) {
	switch (t) {
		case v.STRING: return e;
		case v.INT32:
		case v.FIXED32:
		case v.UINT32:
		case v.SFIXED32:
		case v.SINT32: {
			let t = Number.parseInt(e);
			if (Number.isFinite(t)) return t;
			break;
		}
		case v.BOOL:
			switch (e) {
				case "true": return !0;
				case "false": return !1;
			}
			break;
		case v.UINT64:
		case v.FIXED64:
			try {
				return _.uParse(e);
			} catch {}
			break;
		default: try {
			return _.parse(e);
		} catch {}
	}
	return e;
}
function za(e, t) {
	switch (e.scalar) {
		case v.INT64:
		case v.SFIXED64:
		case v.SINT64:
			"longAsString" in e && e.longAsString && typeof t == "string" && (t = _.parse(t));
			break;
		case v.FIXED64:
		case v.UINT64: "longAsString" in e && e.longAsString && typeof t == "string" && (t = _.uParse(t));
	}
	return t;
}
function Ba(e, t) {
	switch (e.scalar) {
		case v.INT64:
		case v.SFIXED64:
		case v.SINT64:
			"longAsString" in e && e.longAsString ? t = String(t) : (typeof t == "string" || typeof t == "number") && (t = _.parse(t));
			break;
		case v.FIXED64:
		case v.UINT64: "longAsString" in e && e.longAsString ? t = String(t) : (typeof t == "string" || typeof t == "number") && (t = _.uParse(t));
	}
	return t;
}
function Va(e) {
	let t = {
		$typeName: "google.protobuf.Struct",
		fields: {}
	};
	if (Fi(e)) for (let [n, r] of Object.entries(e)) t.fields[n] = Wa(r);
	return t;
}
function Ha(e) {
	let t = {};
	for (let [n, r] of Object.entries(e.fields)) t[n] = Ua(r);
	return t;
}
function Ua(e) {
	switch (e.kind.case) {
		case "structValue": return Ha(e.kind.value);
		case "listValue": return e.kind.value.values.map(Ua);
		case "nullValue":
		case void 0: return null;
		default: return e.kind.value;
	}
}
function Wa(e) {
	let t = {
		$typeName: "google.protobuf.Value",
		kind: { case: void 0 }
	};
	switch (typeof e) {
		case "number":
			t.kind = {
				case: "numberValue",
				value: e
			};
			break;
		case "string":
			t.kind = {
				case: "stringValue",
				value: e
			};
			break;
		case "boolean":
			t.kind = {
				case: "boolValue",
				value: e
			};
			break;
		case "object": if (e === null) t.kind = {
			case: "nullValue",
			value: 0
		};
		else if (Array.isArray(e)) {
			let n = {
				$typeName: "google.protobuf.ListValue",
				values: []
			};
			if (Array.isArray(e)) for (let t of e) n.values.push(Wa(t));
			t.kind = {
				case: "listValue",
				value: n
			};
		} else t.kind = {
			case: "structValue",
			value: Va(e)
		};
	}
	return t;
}
var Ga = 3, Ka = { writeUnknownFields: !0 };
function qa(e) {
	return e ? Object.assign(Object.assign({}, Ka), e) : Ka;
}
function S(e, t, n) {
	return Ja(new Ki(), qa(n), Ea(e, t)).finish();
}
function Ja(e, t, n) {
	for (let r of n.sortedFields) if (n.isSet(r)) Ya(e, t, n, r);
	else if (r.presence == Ga) throw Error(`cannot encode ${r} to binary: required field not set`);
	if (t.writeUnknownFields) for (let { no: t, wireType: r, data: i } of n.getUnknown() ?? []) e.tag(t, r).raw(i);
	return e;
}
function Ya(e, t, n, r) {
	switch (r.fieldKind) {
		case "scalar":
		case "enum":
			Xa(e, n.desc.typeName, r.name, r.scalar ?? v.INT32, r.number, n.get(r));
			break;
		case "list":
			Qa(e, t, r, n.get(r));
			break;
		case "message":
			Za(e, t, r, n.get(r));
			break;
		case "map": for (let [i, a] of n.get(r)) $a(e, t, r, i, a);
	}
}
function Xa(e, t, n, r, i, a) {
	eo(e.tag(i, to(r)), t, n, r, a);
}
function Za(e, t, n, r) {
	n.delimitedEncoding ? Ja(e.tag(n.number, b.StartGroup), t, r).tag(n.number, b.EndGroup) : Ja(e.tag(n.number, b.LengthDelimited).fork(), t, r).join();
}
function Qa(e, t, n, r) {
	if (n.listKind == "message") {
		for (let i of r) Za(e, t, n, i);
		return;
	}
	let i = n.scalar ?? v.INT32;
	if (n.packed) {
		if (!r.size) return;
		e.tag(n.number, b.LengthDelimited).fork();
		for (let t of r) eo(e, n.parent.typeName, n.name, i, t);
		e.join();
	} else for (let t of r) Xa(e, n.parent.typeName, n.name, i, n.number, t);
}
function $a(e, t, n, r, i) {
	switch (e.tag(n.number, b.LengthDelimited).fork(), Xa(e, n.parent.typeName, n.name, n.mapKey, 1, r), n.mapKind) {
		case "scalar":
		case "enum":
			Xa(e, n.parent.typeName, n.name, n.scalar ?? v.INT32, 2, i);
			break;
		case "message": Ja(e.tag(2, b.LengthDelimited).fork(), t, i).join();
	}
	e.join();
}
function eo(e, t, n, r, i) {
	try {
		switch (r) {
			case v.STRING:
				e.string(i);
				break;
			case v.BOOL:
				e.bool(i);
				break;
			case v.DOUBLE:
				e.double(i);
				break;
			case v.FLOAT:
				e.float(i);
				break;
			case v.INT32:
				e.int32(i);
				break;
			case v.INT64:
				e.int64(i);
				break;
			case v.UINT64:
				e.uint64(i);
				break;
			case v.FIXED64:
				e.fixed64(i);
				break;
			case v.BYTES:
				e.bytes(i);
				break;
			case v.FIXED32:
				e.fixed32(i);
				break;
			case v.SFIXED32:
				e.sfixed32(i);
				break;
			case v.SFIXED64:
				e.sfixed64(i);
				break;
			case v.SINT64:
				e.sint64(i);
				break;
			case v.UINT32:
				e.uint32(i);
				break;
			case v.SINT32: e.sint32(i);
		}
	} catch (e) {
		throw e instanceof Error ? Error(`cannot encode field ${t}.${n} to binary: ${e.message}`) : e;
	}
}
function to(e) {
	switch (e) {
		case v.BYTES:
		case v.STRING: return b.LengthDelimited;
		case v.DOUBLE:
		case v.FIXED64:
		case v.SFIXED64: return b.Bit64;
		case v.FIXED32:
		case v.SFIXED32:
		case v.FLOAT: return b.Bit32;
		default: return b.Varint;
	}
}
function no(e, t, ...n) {
	return n.reduce((e, t) => e.nestedMessages[t], e.messages[t]);
}
var ro = /* @__PURE__ */ no(/* @__PURE__ */ xi({
	name: "google/protobuf/descriptor.proto",
	package: "google.protobuf",
	messageType: [
		{
			name: "FileDescriptorSet",
			field: [{
				name: "file",
				number: 1,
				type: 11,
				label: 3,
				typeName: ".google.protobuf.FileDescriptorProto"
			}],
			extensionRange: [{
				start: 536e6,
				end: 536000001
			}]
		},
		{
			name: "FileDescriptorProto",
			field: [
				{
					name: "name",
					number: 1,
					type: 9,
					label: 1
				},
				{
					name: "package",
					number: 2,
					type: 9,
					label: 1
				},
				{
					name: "dependency",
					number: 3,
					type: 9,
					label: 3
				},
				{
					name: "public_dependency",
					number: 10,
					type: 5,
					label: 3
				},
				{
					name: "weak_dependency",
					number: 11,
					type: 5,
					label: 3
				},
				{
					name: "option_dependency",
					number: 15,
					type: 9,
					label: 3
				},
				{
					name: "message_type",
					number: 4,
					type: 11,
					label: 3,
					typeName: ".google.protobuf.DescriptorProto"
				},
				{
					name: "enum_type",
					number: 5,
					type: 11,
					label: 3,
					typeName: ".google.protobuf.EnumDescriptorProto"
				},
				{
					name: "service",
					number: 6,
					type: 11,
					label: 3,
					typeName: ".google.protobuf.ServiceDescriptorProto"
				},
				{
					name: "extension",
					number: 7,
					type: 11,
					label: 3,
					typeName: ".google.protobuf.FieldDescriptorProto"
				},
				{
					name: "options",
					number: 8,
					type: 11,
					label: 1,
					typeName: ".google.protobuf.FileOptions"
				},
				{
					name: "source_code_info",
					number: 9,
					type: 11,
					label: 1,
					typeName: ".google.protobuf.SourceCodeInfo"
				},
				{
					name: "syntax",
					number: 12,
					type: 9,
					label: 1
				},
				{
					name: "edition",
					number: 14,
					type: 14,
					label: 1,
					typeName: ".google.protobuf.Edition"
				}
			]
		},
		{
			name: "DescriptorProto",
			field: [
				{
					name: "name",
					number: 1,
					type: 9,
					label: 1
				},
				{
					name: "field",
					number: 2,
					type: 11,
					label: 3,
					typeName: ".google.protobuf.FieldDescriptorProto"
				},
				{
					name: "extension",
					number: 6,
					type: 11,
					label: 3,
					typeName: ".google.protobuf.FieldDescriptorProto"
				},
				{
					name: "nested_type",
					number: 3,
					type: 11,
					label: 3,
					typeName: ".google.protobuf.DescriptorProto"
				},
				{
					name: "enum_type",
					number: 4,
					type: 11,
					label: 3,
					typeName: ".google.protobuf.EnumDescriptorProto"
				},
				{
					name: "extension_range",
					number: 5,
					type: 11,
					label: 3,
					typeName: ".google.protobuf.DescriptorProto.ExtensionRange"
				},
				{
					name: "oneof_decl",
					number: 8,
					type: 11,
					label: 3,
					typeName: ".google.protobuf.OneofDescriptorProto"
				},
				{
					name: "options",
					number: 7,
					type: 11,
					label: 1,
					typeName: ".google.protobuf.MessageOptions"
				},
				{
					name: "reserved_range",
					number: 9,
					type: 11,
					label: 3,
					typeName: ".google.protobuf.DescriptorProto.ReservedRange"
				},
				{
					name: "reserved_name",
					number: 10,
					type: 9,
					label: 3
				},
				{
					name: "visibility",
					number: 11,
					type: 14,
					label: 1,
					typeName: ".google.protobuf.SymbolVisibility"
				}
			],
			nestedType: [{
				name: "ExtensionRange",
				field: [
					{
						name: "start",
						number: 1,
						type: 5,
						label: 1
					},
					{
						name: "end",
						number: 2,
						type: 5,
						label: 1
					},
					{
						name: "options",
						number: 3,
						type: 11,
						label: 1,
						typeName: ".google.protobuf.ExtensionRangeOptions"
					}
				]
			}, {
				name: "ReservedRange",
				field: [{
					name: "start",
					number: 1,
					type: 5,
					label: 1
				}, {
					name: "end",
					number: 2,
					type: 5,
					label: 1
				}]
			}]
		},
		{
			name: "ExtensionRangeOptions",
			field: [
				{
					name: "uninterpreted_option",
					number: 999,
					type: 11,
					label: 3,
					typeName: ".google.protobuf.UninterpretedOption"
				},
				{
					name: "declaration",
					number: 2,
					type: 11,
					label: 3,
					typeName: ".google.protobuf.ExtensionRangeOptions.Declaration",
					options: { retention: 2 }
				},
				{
					name: "features",
					number: 50,
					type: 11,
					label: 1,
					typeName: ".google.protobuf.FeatureSet"
				},
				{
					name: "verification",
					number: 3,
					type: 14,
					label: 1,
					typeName: ".google.protobuf.ExtensionRangeOptions.VerificationState",
					defaultValue: "UNVERIFIED",
					options: { retention: 2 }
				}
			],
			nestedType: [{
				name: "Declaration",
				field: [
					{
						name: "number",
						number: 1,
						type: 5,
						label: 1
					},
					{
						name: "full_name",
						number: 2,
						type: 9,
						label: 1
					},
					{
						name: "type",
						number: 3,
						type: 9,
						label: 1
					},
					{
						name: "reserved",
						number: 5,
						type: 8,
						label: 1
					},
					{
						name: "repeated",
						number: 6,
						type: 8,
						label: 1
					}
				]
			}],
			enumType: [{
				name: "VerificationState",
				value: [{
					name: "DECLARATION",
					number: 0
				}, {
					name: "UNVERIFIED",
					number: 1
				}]
			}],
			extensionRange: [{
				start: 1e3,
				end: 536870912
			}]
		},
		{
			name: "FieldDescriptorProto",
			field: [
				{
					name: "name",
					number: 1,
					type: 9,
					label: 1
				},
				{
					name: "number",
					number: 3,
					type: 5,
					label: 1
				},
				{
					name: "label",
					number: 4,
					type: 14,
					label: 1,
					typeName: ".google.protobuf.FieldDescriptorProto.Label"
				},
				{
					name: "type",
					number: 5,
					type: 14,
					label: 1,
					typeName: ".google.protobuf.FieldDescriptorProto.Type"
				},
				{
					name: "type_name",
					number: 6,
					type: 9,
					label: 1
				},
				{
					name: "extendee",
					number: 2,
					type: 9,
					label: 1
				},
				{
					name: "default_value",
					number: 7,
					type: 9,
					label: 1
				},
				{
					name: "oneof_index",
					number: 9,
					type: 5,
					label: 1
				},
				{
					name: "json_name",
					number: 10,
					type: 9,
					label: 1
				},
				{
					name: "options",
					number: 8,
					type: 11,
					label: 1,
					typeName: ".google.protobuf.FieldOptions"
				},
				{
					name: "proto3_optional",
					number: 17,
					type: 8,
					label: 1
				}
			],
			enumType: [{
				name: "Type",
				value: [
					{
						name: "TYPE_DOUBLE",
						number: 1
					},
					{
						name: "TYPE_FLOAT",
						number: 2
					},
					{
						name: "TYPE_INT64",
						number: 3
					},
					{
						name: "TYPE_UINT64",
						number: 4
					},
					{
						name: "TYPE_INT32",
						number: 5
					},
					{
						name: "TYPE_FIXED64",
						number: 6
					},
					{
						name: "TYPE_FIXED32",
						number: 7
					},
					{
						name: "TYPE_BOOL",
						number: 8
					},
					{
						name: "TYPE_STRING",
						number: 9
					},
					{
						name: "TYPE_GROUP",
						number: 10
					},
					{
						name: "TYPE_MESSAGE",
						number: 11
					},
					{
						name: "TYPE_BYTES",
						number: 12
					},
					{
						name: "TYPE_UINT32",
						number: 13
					},
					{
						name: "TYPE_ENUM",
						number: 14
					},
					{
						name: "TYPE_SFIXED32",
						number: 15
					},
					{
						name: "TYPE_SFIXED64",
						number: 16
					},
					{
						name: "TYPE_SINT32",
						number: 17
					},
					{
						name: "TYPE_SINT64",
						number: 18
					}
				]
			}, {
				name: "Label",
				value: [
					{
						name: "LABEL_OPTIONAL",
						number: 1
					},
					{
						name: "LABEL_REPEATED",
						number: 3
					},
					{
						name: "LABEL_REQUIRED",
						number: 2
					}
				]
			}]
		},
		{
			name: "OneofDescriptorProto",
			field: [{
				name: "name",
				number: 1,
				type: 9,
				label: 1
			}, {
				name: "options",
				number: 2,
				type: 11,
				label: 1,
				typeName: ".google.protobuf.OneofOptions"
			}]
		},
		{
			name: "EnumDescriptorProto",
			field: [
				{
					name: "name",
					number: 1,
					type: 9,
					label: 1
				},
				{
					name: "value",
					number: 2,
					type: 11,
					label: 3,
					typeName: ".google.protobuf.EnumValueDescriptorProto"
				},
				{
					name: "options",
					number: 3,
					type: 11,
					label: 1,
					typeName: ".google.protobuf.EnumOptions"
				},
				{
					name: "reserved_range",
					number: 4,
					type: 11,
					label: 3,
					typeName: ".google.protobuf.EnumDescriptorProto.EnumReservedRange"
				},
				{
					name: "reserved_name",
					number: 5,
					type: 9,
					label: 3
				},
				{
					name: "visibility",
					number: 6,
					type: 14,
					label: 1,
					typeName: ".google.protobuf.SymbolVisibility"
				}
			],
			nestedType: [{
				name: "EnumReservedRange",
				field: [{
					name: "start",
					number: 1,
					type: 5,
					label: 1
				}, {
					name: "end",
					number: 2,
					type: 5,
					label: 1
				}]
			}]
		},
		{
			name: "EnumValueDescriptorProto",
			field: [
				{
					name: "name",
					number: 1,
					type: 9,
					label: 1
				},
				{
					name: "number",
					number: 2,
					type: 5,
					label: 1
				},
				{
					name: "options",
					number: 3,
					type: 11,
					label: 1,
					typeName: ".google.protobuf.EnumValueOptions"
				}
			]
		},
		{
			name: "ServiceDescriptorProto",
			field: [
				{
					name: "name",
					number: 1,
					type: 9,
					label: 1
				},
				{
					name: "method",
					number: 2,
					type: 11,
					label: 3,
					typeName: ".google.protobuf.MethodDescriptorProto"
				},
				{
					name: "options",
					number: 3,
					type: 11,
					label: 1,
					typeName: ".google.protobuf.ServiceOptions"
				}
			]
		},
		{
			name: "MethodDescriptorProto",
			field: [
				{
					name: "name",
					number: 1,
					type: 9,
					label: 1
				},
				{
					name: "input_type",
					number: 2,
					type: 9,
					label: 1
				},
				{
					name: "output_type",
					number: 3,
					type: 9,
					label: 1
				},
				{
					name: "options",
					number: 4,
					type: 11,
					label: 1,
					typeName: ".google.protobuf.MethodOptions"
				},
				{
					name: "client_streaming",
					number: 5,
					type: 8,
					label: 1,
					defaultValue: "false"
				},
				{
					name: "server_streaming",
					number: 6,
					type: 8,
					label: 1,
					defaultValue: "false"
				}
			]
		},
		{
			name: "FileOptions",
			field: [
				{
					name: "java_package",
					number: 1,
					type: 9,
					label: 1
				},
				{
					name: "java_outer_classname",
					number: 8,
					type: 9,
					label: 1
				},
				{
					name: "java_multiple_files",
					number: 10,
					type: 8,
					label: 1,
					defaultValue: "false"
				},
				{
					name: "java_generate_equals_and_hash",
					number: 20,
					type: 8,
					label: 1,
					options: { deprecated: !0 }
				},
				{
					name: "java_string_check_utf8",
					number: 27,
					type: 8,
					label: 1,
					defaultValue: "false"
				},
				{
					name: "optimize_for",
					number: 9,
					type: 14,
					label: 1,
					typeName: ".google.protobuf.FileOptions.OptimizeMode",
					defaultValue: "SPEED"
				},
				{
					name: "go_package",
					number: 11,
					type: 9,
					label: 1
				},
				{
					name: "cc_generic_services",
					number: 16,
					type: 8,
					label: 1,
					defaultValue: "false"
				},
				{
					name: "java_generic_services",
					number: 17,
					type: 8,
					label: 1,
					defaultValue: "false"
				},
				{
					name: "py_generic_services",
					number: 18,
					type: 8,
					label: 1,
					defaultValue: "false"
				},
				{
					name: "deprecated",
					number: 23,
					type: 8,
					label: 1,
					defaultValue: "false"
				},
				{
					name: "cc_enable_arenas",
					number: 31,
					type: 8,
					label: 1,
					defaultValue: "true"
				},
				{
					name: "objc_class_prefix",
					number: 36,
					type: 9,
					label: 1
				},
				{
					name: "csharp_namespace",
					number: 37,
					type: 9,
					label: 1
				},
				{
					name: "swift_prefix",
					number: 39,
					type: 9,
					label: 1
				},
				{
					name: "php_class_prefix",
					number: 40,
					type: 9,
					label: 1
				},
				{
					name: "php_namespace",
					number: 41,
					type: 9,
					label: 1
				},
				{
					name: "php_metadata_namespace",
					number: 44,
					type: 9,
					label: 1
				},
				{
					name: "ruby_package",
					number: 45,
					type: 9,
					label: 1
				},
				{
					name: "features",
					number: 50,
					type: 11,
					label: 1,
					typeName: ".google.protobuf.FeatureSet"
				},
				{
					name: "uninterpreted_option",
					number: 999,
					type: 11,
					label: 3,
					typeName: ".google.protobuf.UninterpretedOption"
				}
			],
			enumType: [{
				name: "OptimizeMode",
				value: [
					{
						name: "SPEED",
						number: 1
					},
					{
						name: "CODE_SIZE",
						number: 2
					},
					{
						name: "LITE_RUNTIME",
						number: 3
					}
				]
			}],
			extensionRange: [{
				start: 1e3,
				end: 536870912
			}]
		},
		{
			name: "MessageOptions",
			field: [
				{
					name: "message_set_wire_format",
					number: 1,
					type: 8,
					label: 1,
					defaultValue: "false"
				},
				{
					name: "no_standard_descriptor_accessor",
					number: 2,
					type: 8,
					label: 1,
					defaultValue: "false"
				},
				{
					name: "deprecated",
					number: 3,
					type: 8,
					label: 1,
					defaultValue: "false"
				},
				{
					name: "map_entry",
					number: 7,
					type: 8,
					label: 1
				},
				{
					name: "deprecated_legacy_json_field_conflicts",
					number: 11,
					type: 8,
					label: 1,
					options: { deprecated: !0 }
				},
				{
					name: "features",
					number: 12,
					type: 11,
					label: 1,
					typeName: ".google.protobuf.FeatureSet"
				},
				{
					name: "uninterpreted_option",
					number: 999,
					type: 11,
					label: 3,
					typeName: ".google.protobuf.UninterpretedOption"
				}
			],
			extensionRange: [{
				start: 1e3,
				end: 536870912
			}]
		},
		{
			name: "FieldOptions",
			field: [
				{
					name: "ctype",
					number: 1,
					type: 14,
					label: 1,
					typeName: ".google.protobuf.FieldOptions.CType",
					defaultValue: "STRING"
				},
				{
					name: "packed",
					number: 2,
					type: 8,
					label: 1
				},
				{
					name: "jstype",
					number: 6,
					type: 14,
					label: 1,
					typeName: ".google.protobuf.FieldOptions.JSType",
					defaultValue: "JS_NORMAL"
				},
				{
					name: "lazy",
					number: 5,
					type: 8,
					label: 1,
					defaultValue: "false"
				},
				{
					name: "unverified_lazy",
					number: 15,
					type: 8,
					label: 1,
					defaultValue: "false"
				},
				{
					name: "deprecated",
					number: 3,
					type: 8,
					label: 1,
					defaultValue: "false"
				},
				{
					name: "weak",
					number: 10,
					type: 8,
					label: 1,
					defaultValue: "false",
					options: { deprecated: !0 }
				},
				{
					name: "debug_redact",
					number: 16,
					type: 8,
					label: 1,
					defaultValue: "false"
				},
				{
					name: "retention",
					number: 17,
					type: 14,
					label: 1,
					typeName: ".google.protobuf.FieldOptions.OptionRetention"
				},
				{
					name: "targets",
					number: 19,
					type: 14,
					label: 3,
					typeName: ".google.protobuf.FieldOptions.OptionTargetType"
				},
				{
					name: "edition_defaults",
					number: 20,
					type: 11,
					label: 3,
					typeName: ".google.protobuf.FieldOptions.EditionDefault"
				},
				{
					name: "features",
					number: 21,
					type: 11,
					label: 1,
					typeName: ".google.protobuf.FeatureSet"
				},
				{
					name: "feature_support",
					number: 22,
					type: 11,
					label: 1,
					typeName: ".google.protobuf.FieldOptions.FeatureSupport"
				},
				{
					name: "uninterpreted_option",
					number: 999,
					type: 11,
					label: 3,
					typeName: ".google.protobuf.UninterpretedOption"
				}
			],
			nestedType: [{
				name: "EditionDefault",
				field: [{
					name: "edition",
					number: 3,
					type: 14,
					label: 1,
					typeName: ".google.protobuf.Edition"
				}, {
					name: "value",
					number: 2,
					type: 9,
					label: 1
				}]
			}, {
				name: "FeatureSupport",
				field: [
					{
						name: "edition_introduced",
						number: 1,
						type: 14,
						label: 1,
						typeName: ".google.protobuf.Edition"
					},
					{
						name: "edition_deprecated",
						number: 2,
						type: 14,
						label: 1,
						typeName: ".google.protobuf.Edition"
					},
					{
						name: "deprecation_warning",
						number: 3,
						type: 9,
						label: 1
					},
					{
						name: "edition_removed",
						number: 4,
						type: 14,
						label: 1,
						typeName: ".google.protobuf.Edition"
					}
				]
			}],
			enumType: [
				{
					name: "CType",
					value: [
						{
							name: "STRING",
							number: 0
						},
						{
							name: "CORD",
							number: 1
						},
						{
							name: "STRING_PIECE",
							number: 2
						}
					]
				},
				{
					name: "JSType",
					value: [
						{
							name: "JS_NORMAL",
							number: 0
						},
						{
							name: "JS_STRING",
							number: 1
						},
						{
							name: "JS_NUMBER",
							number: 2
						}
					]
				},
				{
					name: "OptionRetention",
					value: [
						{
							name: "RETENTION_UNKNOWN",
							number: 0
						},
						{
							name: "RETENTION_RUNTIME",
							number: 1
						},
						{
							name: "RETENTION_SOURCE",
							number: 2
						}
					]
				},
				{
					name: "OptionTargetType",
					value: [
						{
							name: "TARGET_TYPE_UNKNOWN",
							number: 0
						},
						{
							name: "TARGET_TYPE_FILE",
							number: 1
						},
						{
							name: "TARGET_TYPE_EXTENSION_RANGE",
							number: 2
						},
						{
							name: "TARGET_TYPE_MESSAGE",
							number: 3
						},
						{
							name: "TARGET_TYPE_FIELD",
							number: 4
						},
						{
							name: "TARGET_TYPE_ONEOF",
							number: 5
						},
						{
							name: "TARGET_TYPE_ENUM",
							number: 6
						},
						{
							name: "TARGET_TYPE_ENUM_ENTRY",
							number: 7
						},
						{
							name: "TARGET_TYPE_SERVICE",
							number: 8
						},
						{
							name: "TARGET_TYPE_METHOD",
							number: 9
						}
					]
				}
			],
			extensionRange: [{
				start: 1e3,
				end: 536870912
			}]
		},
		{
			name: "OneofOptions",
			field: [{
				name: "features",
				number: 1,
				type: 11,
				label: 1,
				typeName: ".google.protobuf.FeatureSet"
			}, {
				name: "uninterpreted_option",
				number: 999,
				type: 11,
				label: 3,
				typeName: ".google.protobuf.UninterpretedOption"
			}],
			extensionRange: [{
				start: 1e3,
				end: 536870912
			}]
		},
		{
			name: "EnumOptions",
			field: [
				{
					name: "allow_alias",
					number: 2,
					type: 8,
					label: 1
				},
				{
					name: "deprecated",
					number: 3,
					type: 8,
					label: 1,
					defaultValue: "false"
				},
				{
					name: "deprecated_legacy_json_field_conflicts",
					number: 6,
					type: 8,
					label: 1,
					options: { deprecated: !0 }
				},
				{
					name: "features",
					number: 7,
					type: 11,
					label: 1,
					typeName: ".google.protobuf.FeatureSet"
				},
				{
					name: "uninterpreted_option",
					number: 999,
					type: 11,
					label: 3,
					typeName: ".google.protobuf.UninterpretedOption"
				}
			],
			extensionRange: [{
				start: 1e3,
				end: 536870912
			}]
		},
		{
			name: "EnumValueOptions",
			field: [
				{
					name: "deprecated",
					number: 1,
					type: 8,
					label: 1,
					defaultValue: "false"
				},
				{
					name: "features",
					number: 2,
					type: 11,
					label: 1,
					typeName: ".google.protobuf.FeatureSet"
				},
				{
					name: "debug_redact",
					number: 3,
					type: 8,
					label: 1,
					defaultValue: "false"
				},
				{
					name: "feature_support",
					number: 4,
					type: 11,
					label: 1,
					typeName: ".google.protobuf.FieldOptions.FeatureSupport"
				},
				{
					name: "uninterpreted_option",
					number: 999,
					type: 11,
					label: 3,
					typeName: ".google.protobuf.UninterpretedOption"
				}
			],
			extensionRange: [{
				start: 1e3,
				end: 536870912
			}]
		},
		{
			name: "ServiceOptions",
			field: [
				{
					name: "features",
					number: 34,
					type: 11,
					label: 1,
					typeName: ".google.protobuf.FeatureSet"
				},
				{
					name: "deprecated",
					number: 33,
					type: 8,
					label: 1,
					defaultValue: "false"
				},
				{
					name: "uninterpreted_option",
					number: 999,
					type: 11,
					label: 3,
					typeName: ".google.protobuf.UninterpretedOption"
				}
			],
			extensionRange: [{
				start: 1e3,
				end: 536870912
			}]
		},
		{
			name: "MethodOptions",
			field: [
				{
					name: "deprecated",
					number: 33,
					type: 8,
					label: 1,
					defaultValue: "false"
				},
				{
					name: "idempotency_level",
					number: 34,
					type: 14,
					label: 1,
					typeName: ".google.protobuf.MethodOptions.IdempotencyLevel",
					defaultValue: "IDEMPOTENCY_UNKNOWN"
				},
				{
					name: "features",
					number: 35,
					type: 11,
					label: 1,
					typeName: ".google.protobuf.FeatureSet"
				},
				{
					name: "uninterpreted_option",
					number: 999,
					type: 11,
					label: 3,
					typeName: ".google.protobuf.UninterpretedOption"
				}
			],
			enumType: [{
				name: "IdempotencyLevel",
				value: [
					{
						name: "IDEMPOTENCY_UNKNOWN",
						number: 0
					},
					{
						name: "NO_SIDE_EFFECTS",
						number: 1
					},
					{
						name: "IDEMPOTENT",
						number: 2
					}
				]
			}],
			extensionRange: [{
				start: 1e3,
				end: 536870912
			}]
		},
		{
			name: "UninterpretedOption",
			field: [
				{
					name: "name",
					number: 2,
					type: 11,
					label: 3,
					typeName: ".google.protobuf.UninterpretedOption.NamePart"
				},
				{
					name: "identifier_value",
					number: 3,
					type: 9,
					label: 1
				},
				{
					name: "positive_int_value",
					number: 4,
					type: 4,
					label: 1
				},
				{
					name: "negative_int_value",
					number: 5,
					type: 3,
					label: 1
				},
				{
					name: "double_value",
					number: 6,
					type: 1,
					label: 1
				},
				{
					name: "string_value",
					number: 7,
					type: 12,
					label: 1
				},
				{
					name: "aggregate_value",
					number: 8,
					type: 9,
					label: 1
				}
			],
			nestedType: [{
				name: "NamePart",
				field: [{
					name: "name_part",
					number: 1,
					type: 9,
					label: 2
				}, {
					name: "is_extension",
					number: 2,
					type: 8,
					label: 2
				}]
			}]
		},
		{
			name: "FeatureSet",
			field: [
				{
					name: "field_presence",
					number: 1,
					type: 14,
					label: 1,
					typeName: ".google.protobuf.FeatureSet.FieldPresence",
					options: {
						retention: 1,
						targets: [4, 1],
						editionDefaults: [
							{
								value: "EXPLICIT",
								edition: 900
							},
							{
								value: "IMPLICIT",
								edition: 999
							},
							{
								value: "EXPLICIT",
								edition: 1e3
							}
						]
					}
				},
				{
					name: "enum_type",
					number: 2,
					type: 14,
					label: 1,
					typeName: ".google.protobuf.FeatureSet.EnumType",
					options: {
						retention: 1,
						targets: [6, 1],
						editionDefaults: [{
							value: "CLOSED",
							edition: 900
						}, {
							value: "OPEN",
							edition: 999
						}]
					}
				},
				{
					name: "repeated_field_encoding",
					number: 3,
					type: 14,
					label: 1,
					typeName: ".google.protobuf.FeatureSet.RepeatedFieldEncoding",
					options: {
						retention: 1,
						targets: [4, 1],
						editionDefaults: [{
							value: "EXPANDED",
							edition: 900
						}, {
							value: "PACKED",
							edition: 999
						}]
					}
				},
				{
					name: "utf8_validation",
					number: 4,
					type: 14,
					label: 1,
					typeName: ".google.protobuf.FeatureSet.Utf8Validation",
					options: {
						retention: 1,
						targets: [4, 1],
						editionDefaults: [{
							value: "NONE",
							edition: 900
						}, {
							value: "VERIFY",
							edition: 999
						}]
					}
				},
				{
					name: "message_encoding",
					number: 5,
					type: 14,
					label: 1,
					typeName: ".google.protobuf.FeatureSet.MessageEncoding",
					options: {
						retention: 1,
						targets: [4, 1],
						editionDefaults: [{
							value: "LENGTH_PREFIXED",
							edition: 900
						}]
					}
				},
				{
					name: "json_format",
					number: 6,
					type: 14,
					label: 1,
					typeName: ".google.protobuf.FeatureSet.JsonFormat",
					options: {
						retention: 1,
						targets: [
							3,
							6,
							1
						],
						editionDefaults: [{
							value: "LEGACY_BEST_EFFORT",
							edition: 900
						}, {
							value: "ALLOW",
							edition: 999
						}]
					}
				},
				{
					name: "enforce_naming_style",
					number: 7,
					type: 14,
					label: 1,
					typeName: ".google.protobuf.FeatureSet.EnforceNamingStyle",
					options: {
						retention: 2,
						targets: [
							1,
							2,
							3,
							4,
							5,
							6,
							7,
							8,
							9
						],
						editionDefaults: [{
							value: "STYLE_LEGACY",
							edition: 900
						}, {
							value: "STYLE2024",
							edition: 1001
						}]
					}
				},
				{
					name: "default_symbol_visibility",
					number: 8,
					type: 14,
					label: 1,
					typeName: ".google.protobuf.FeatureSet.VisibilityFeature.DefaultSymbolVisibility",
					options: {
						retention: 2,
						targets: [1],
						editionDefaults: [{
							value: "EXPORT_ALL",
							edition: 900
						}, {
							value: "EXPORT_TOP_LEVEL",
							edition: 1001
						}]
					}
				}
			],
			nestedType: [{
				name: "VisibilityFeature",
				enumType: [{
					name: "DefaultSymbolVisibility",
					value: [
						{
							name: "DEFAULT_SYMBOL_VISIBILITY_UNKNOWN",
							number: 0
						},
						{
							name: "EXPORT_ALL",
							number: 1
						},
						{
							name: "EXPORT_TOP_LEVEL",
							number: 2
						},
						{
							name: "LOCAL_ALL",
							number: 3
						},
						{
							name: "STRICT",
							number: 4
						}
					]
				}]
			}],
			enumType: [
				{
					name: "FieldPresence",
					value: [
						{
							name: "FIELD_PRESENCE_UNKNOWN",
							number: 0
						},
						{
							name: "EXPLICIT",
							number: 1
						},
						{
							name: "IMPLICIT",
							number: 2
						},
						{
							name: "LEGACY_REQUIRED",
							number: 3
						}
					]
				},
				{
					name: "EnumType",
					value: [
						{
							name: "ENUM_TYPE_UNKNOWN",
							number: 0
						},
						{
							name: "OPEN",
							number: 1
						},
						{
							name: "CLOSED",
							number: 2
						}
					]
				},
				{
					name: "RepeatedFieldEncoding",
					value: [
						{
							name: "REPEATED_FIELD_ENCODING_UNKNOWN",
							number: 0
						},
						{
							name: "PACKED",
							number: 1
						},
						{
							name: "EXPANDED",
							number: 2
						}
					]
				},
				{
					name: "Utf8Validation",
					value: [
						{
							name: "UTF8_VALIDATION_UNKNOWN",
							number: 0
						},
						{
							name: "VERIFY",
							number: 2
						},
						{
							name: "NONE",
							number: 3
						}
					]
				},
				{
					name: "MessageEncoding",
					value: [
						{
							name: "MESSAGE_ENCODING_UNKNOWN",
							number: 0
						},
						{
							name: "LENGTH_PREFIXED",
							number: 1
						},
						{
							name: "DELIMITED",
							number: 2
						}
					]
				},
				{
					name: "JsonFormat",
					value: [
						{
							name: "JSON_FORMAT_UNKNOWN",
							number: 0
						},
						{
							name: "ALLOW",
							number: 1
						},
						{
							name: "LEGACY_BEST_EFFORT",
							number: 2
						}
					]
				},
				{
					name: "EnforceNamingStyle",
					value: [
						{
							name: "ENFORCE_NAMING_STYLE_UNKNOWN",
							number: 0
						},
						{
							name: "STYLE2024",
							number: 1
						},
						{
							name: "STYLE_LEGACY",
							number: 2
						}
					]
				}
			],
			extensionRange: [
				{
					start: 1e3,
					end: 9995
				},
				{
					start: 9995,
					end: 1e4
				},
				{
					start: 1e4,
					end: 10001
				}
			]
		},
		{
			name: "FeatureSetDefaults",
			field: [
				{
					name: "defaults",
					number: 1,
					type: 11,
					label: 3,
					typeName: ".google.protobuf.FeatureSetDefaults.FeatureSetEditionDefault"
				},
				{
					name: "minimum_edition",
					number: 4,
					type: 14,
					label: 1,
					typeName: ".google.protobuf.Edition"
				},
				{
					name: "maximum_edition",
					number: 5,
					type: 14,
					label: 1,
					typeName: ".google.protobuf.Edition"
				}
			],
			nestedType: [{
				name: "FeatureSetEditionDefault",
				field: [
					{
						name: "edition",
						number: 3,
						type: 14,
						label: 1,
						typeName: ".google.protobuf.Edition"
					},
					{
						name: "overridable_features",
						number: 4,
						type: 11,
						label: 1,
						typeName: ".google.protobuf.FeatureSet"
					},
					{
						name: "fixed_features",
						number: 5,
						type: 11,
						label: 1,
						typeName: ".google.protobuf.FeatureSet"
					}
				]
			}]
		},
		{
			name: "SourceCodeInfo",
			field: [{
				name: "location",
				number: 1,
				type: 11,
				label: 3,
				typeName: ".google.protobuf.SourceCodeInfo.Location"
			}],
			nestedType: [{
				name: "Location",
				field: [
					{
						name: "path",
						number: 1,
						type: 5,
						label: 3,
						options: { packed: !0 }
					},
					{
						name: "span",
						number: 2,
						type: 5,
						label: 3,
						options: { packed: !0 }
					},
					{
						name: "leading_comments",
						number: 3,
						type: 9,
						label: 1
					},
					{
						name: "trailing_comments",
						number: 4,
						type: 9,
						label: 1
					},
					{
						name: "leading_detached_comments",
						number: 6,
						type: 9,
						label: 3
					}
				]
			}],
			extensionRange: [{
				start: 536e6,
				end: 536000001
			}]
		},
		{
			name: "GeneratedCodeInfo",
			field: [{
				name: "annotation",
				number: 1,
				type: 11,
				label: 3,
				typeName: ".google.protobuf.GeneratedCodeInfo.Annotation"
			}],
			nestedType: [{
				name: "Annotation",
				field: [
					{
						name: "path",
						number: 1,
						type: 5,
						label: 3,
						options: { packed: !0 }
					},
					{
						name: "source_file",
						number: 2,
						type: 9,
						label: 1
					},
					{
						name: "begin",
						number: 3,
						type: 5,
						label: 1
					},
					{
						name: "end",
						number: 4,
						type: 5,
						label: 1
					},
					{
						name: "semantic",
						number: 5,
						type: 14,
						label: 1,
						typeName: ".google.protobuf.GeneratedCodeInfo.Annotation.Semantic"
					}
				],
				enumType: [{
					name: "Semantic",
					value: [
						{
							name: "NONE",
							number: 0
						},
						{
							name: "SET",
							number: 1
						},
						{
							name: "ALIAS",
							number: 2
						}
					]
				}]
			}]
		}
	],
	enumType: [{
		name: "Edition",
		value: [
			{
				name: "EDITION_UNKNOWN",
				number: 0
			},
			{
				name: "EDITION_LEGACY",
				number: 900
			},
			{
				name: "EDITION_PROTO2",
				number: 998
			},
			{
				name: "EDITION_PROTO3",
				number: 999
			},
			{
				name: "EDITION_2023",
				number: 1e3
			},
			{
				name: "EDITION_2024",
				number: 1001
			},
			{
				name: "EDITION_1_TEST_ONLY",
				number: 1
			},
			{
				name: "EDITION_2_TEST_ONLY",
				number: 2
			},
			{
				name: "EDITION_99997_TEST_ONLY",
				number: 99997
			},
			{
				name: "EDITION_99998_TEST_ONLY",
				number: 99998
			},
			{
				name: "EDITION_99999_TEST_ONLY",
				number: 99999
			},
			{
				name: "EDITION_MAX",
				number: 2147483647
			}
		]
	}, {
		name: "SymbolVisibility",
		value: [
			{
				name: "VISIBILITY_UNSET",
				number: 0
			},
			{
				name: "VISIBILITY_LOCAL",
				number: 1
			},
			{
				name: "VISIBILITY_EXPORT",
				number: 2
			}
		]
	}]
}), 1), io;
(function(e) {
	e[e.DECLARATION = 0] = "DECLARATION", e[e.UNVERIFIED = 1] = "UNVERIFIED";
})(io ||= {});
var ao;
(function(e) {
	e[e.DOUBLE = 1] = "DOUBLE", e[e.FLOAT = 2] = "FLOAT", e[e.INT64 = 3] = "INT64", e[e.UINT64 = 4] = "UINT64", e[e.INT32 = 5] = "INT32", e[e.FIXED64 = 6] = "FIXED64", e[e.FIXED32 = 7] = "FIXED32", e[e.BOOL = 8] = "BOOL", e[e.STRING = 9] = "STRING", e[e.GROUP = 10] = "GROUP", e[e.MESSAGE = 11] = "MESSAGE", e[e.BYTES = 12] = "BYTES", e[e.UINT32 = 13] = "UINT32", e[e.ENUM = 14] = "ENUM", e[e.SFIXED32 = 15] = "SFIXED32", e[e.SFIXED64 = 16] = "SFIXED64", e[e.SINT32 = 17] = "SINT32", e[e.SINT64 = 18] = "SINT64";
})(ao ||= {});
var oo;
(function(e) {
	e[e.OPTIONAL = 1] = "OPTIONAL", e[e.REPEATED = 3] = "REPEATED", e[e.REQUIRED = 2] = "REQUIRED";
})(oo ||= {});
var so;
(function(e) {
	e[e.SPEED = 1] = "SPEED", e[e.CODE_SIZE = 2] = "CODE_SIZE", e[e.LITE_RUNTIME = 3] = "LITE_RUNTIME";
})(so ||= {});
var co;
(function(e) {
	e[e.STRING = 0] = "STRING", e[e.CORD = 1] = "CORD", e[e.STRING_PIECE = 2] = "STRING_PIECE";
})(co ||= {});
var lo;
(function(e) {
	e[e.JS_NORMAL = 0] = "JS_NORMAL", e[e.JS_STRING = 1] = "JS_STRING", e[e.JS_NUMBER = 2] = "JS_NUMBER";
})(lo ||= {});
var uo;
(function(e) {
	e[e.RETENTION_UNKNOWN = 0] = "RETENTION_UNKNOWN", e[e.RETENTION_RUNTIME = 1] = "RETENTION_RUNTIME", e[e.RETENTION_SOURCE = 2] = "RETENTION_SOURCE";
})(uo ||= {});
var fo;
(function(e) {
	e[e.TARGET_TYPE_UNKNOWN = 0] = "TARGET_TYPE_UNKNOWN", e[e.TARGET_TYPE_FILE = 1] = "TARGET_TYPE_FILE", e[e.TARGET_TYPE_EXTENSION_RANGE = 2] = "TARGET_TYPE_EXTENSION_RANGE", e[e.TARGET_TYPE_MESSAGE = 3] = "TARGET_TYPE_MESSAGE", e[e.TARGET_TYPE_FIELD = 4] = "TARGET_TYPE_FIELD", e[e.TARGET_TYPE_ONEOF = 5] = "TARGET_TYPE_ONEOF", e[e.TARGET_TYPE_ENUM = 6] = "TARGET_TYPE_ENUM", e[e.TARGET_TYPE_ENUM_ENTRY = 7] = "TARGET_TYPE_ENUM_ENTRY", e[e.TARGET_TYPE_SERVICE = 8] = "TARGET_TYPE_SERVICE", e[e.TARGET_TYPE_METHOD = 9] = "TARGET_TYPE_METHOD";
})(fo ||= {});
var po;
(function(e) {
	e[e.IDEMPOTENCY_UNKNOWN = 0] = "IDEMPOTENCY_UNKNOWN", e[e.NO_SIDE_EFFECTS = 1] = "NO_SIDE_EFFECTS", e[e.IDEMPOTENT = 2] = "IDEMPOTENT";
})(po ||= {});
var mo;
(function(e) {
	e[e.DEFAULT_SYMBOL_VISIBILITY_UNKNOWN = 0] = "DEFAULT_SYMBOL_VISIBILITY_UNKNOWN", e[e.EXPORT_ALL = 1] = "EXPORT_ALL", e[e.EXPORT_TOP_LEVEL = 2] = "EXPORT_TOP_LEVEL", e[e.LOCAL_ALL = 3] = "LOCAL_ALL", e[e.STRICT = 4] = "STRICT";
})(mo ||= {});
var ho;
(function(e) {
	e[e.FIELD_PRESENCE_UNKNOWN = 0] = "FIELD_PRESENCE_UNKNOWN", e[e.EXPLICIT = 1] = "EXPLICIT", e[e.IMPLICIT = 2] = "IMPLICIT", e[e.LEGACY_REQUIRED = 3] = "LEGACY_REQUIRED";
})(ho ||= {});
var go;
(function(e) {
	e[e.ENUM_TYPE_UNKNOWN = 0] = "ENUM_TYPE_UNKNOWN", e[e.OPEN = 1] = "OPEN", e[e.CLOSED = 2] = "CLOSED";
})(go ||= {});
var _o;
(function(e) {
	e[e.REPEATED_FIELD_ENCODING_UNKNOWN = 0] = "REPEATED_FIELD_ENCODING_UNKNOWN", e[e.PACKED = 1] = "PACKED", e[e.EXPANDED = 2] = "EXPANDED";
})(_o ||= {});
var vo;
(function(e) {
	e[e.UTF8_VALIDATION_UNKNOWN = 0] = "UTF8_VALIDATION_UNKNOWN", e[e.VERIFY = 2] = "VERIFY", e[e.NONE = 3] = "NONE";
})(vo ||= {});
var yo;
(function(e) {
	e[e.MESSAGE_ENCODING_UNKNOWN = 0] = "MESSAGE_ENCODING_UNKNOWN", e[e.LENGTH_PREFIXED = 1] = "LENGTH_PREFIXED", e[e.DELIMITED = 2] = "DELIMITED";
})(yo ||= {});
var bo;
(function(e) {
	e[e.JSON_FORMAT_UNKNOWN = 0] = "JSON_FORMAT_UNKNOWN", e[e.ALLOW = 1] = "ALLOW", e[e.LEGACY_BEST_EFFORT = 2] = "LEGACY_BEST_EFFORT";
})(bo ||= {});
var xo;
(function(e) {
	e[e.ENFORCE_NAMING_STYLE_UNKNOWN = 0] = "ENFORCE_NAMING_STYLE_UNKNOWN", e[e.STYLE2024 = 1] = "STYLE2024", e[e.STYLE_LEGACY = 2] = "STYLE_LEGACY";
})(xo ||= {});
var So;
(function(e) {
	e[e.NONE = 0] = "NONE", e[e.SET = 1] = "SET", e[e.ALIAS = 2] = "ALIAS";
})(So ||= {});
var Co;
(function(e) {
	e[e.EDITION_UNKNOWN = 0] = "EDITION_UNKNOWN", e[e.EDITION_LEGACY = 900] = "EDITION_LEGACY", e[e.EDITION_PROTO2 = 998] = "EDITION_PROTO2", e[e.EDITION_PROTO3 = 999] = "EDITION_PROTO3", e[e.EDITION_2023 = 1e3] = "EDITION_2023", e[e.EDITION_2024 = 1001] = "EDITION_2024", e[e.EDITION_1_TEST_ONLY = 1] = "EDITION_1_TEST_ONLY", e[e.EDITION_2_TEST_ONLY = 2] = "EDITION_2_TEST_ONLY", e[e.EDITION_99997_TEST_ONLY = 99997] = "EDITION_99997_TEST_ONLY", e[e.EDITION_99998_TEST_ONLY = 99998] = "EDITION_99998_TEST_ONLY", e[e.EDITION_99999_TEST_ONLY = 99999] = "EDITION_99999_TEST_ONLY", e[e.EDITION_MAX = 2147483647] = "EDITION_MAX";
})(Co ||= {});
var wo;
(function(e) {
	e[e.VISIBILITY_UNSET = 0] = "VISIBILITY_UNSET", e[e.VISIBILITY_LOCAL = 1] = "VISIBILITY_LOCAL", e[e.VISIBILITY_EXPORT = 2] = "VISIBILITY_EXPORT";
})(wo ||= {});
function C(e, t, ...n) {
	if (n.length == 0) return e.enums[t];
	let r = n.pop();
	return n.reduce((e, t) => e.nestedMessages[t], e.messages[t]).nestedEnums[r];
}
var To = { readUnknownFields: !0 };
function Eo(e) {
	return e ? Object.assign(Object.assign({}, To), e) : To;
}
function w(e, t, n) {
	let r = Ea(e, void 0, !1);
	return Do(r, new qi(t), Eo(n), !1, t.byteLength), r.message;
}
function Do(e, t, n, r, i) {
	let a = r ? t.len : t.pos + i, o, s, c = e.getUnknown() ?? [];
	for (; t.pos < a && ([o, s] = t.tag(), !(r && s == b.EndGroup));) {
		let r = e.findNumber(o);
		if (r) Oo(e, t, r, s, n);
		else {
			let e = t.skip(s, o);
			n.readUnknownFields && c.push({
				no: o,
				wireType: s,
				data: e
			});
		}
	}
	if (r && (s != b.EndGroup || o !== i)) throw Error("invalid end group tag");
	c.length > 0 && e.setUnknown(c);
}
function Oo(e, t, n, r, i) {
	switch (n.fieldKind) {
		case "scalar":
			e.set(n, Mo(t, n.scalar));
			break;
		case "enum":
			let a = Mo(t, v.INT32);
			if (n.enum.open) e.set(n, a);
			else if (n.enum.values.some((e) => e.number === a)) e.set(n, a);
			else if (i.readUnknownFields) {
				let t = [];
				pr(a, t);
				let i = e.getUnknown() ?? [];
				i.push({
					no: n.number,
					wireType: r,
					data: new Uint8Array(t)
				}), e.setUnknown(i);
			}
			break;
		case "message":
			e.set(n, jo(t, i, n, e.get(n)));
			break;
		case "list":
			Ao(t, r, e.get(n), i);
			break;
		case "map": ko(t, e.get(n), i);
	}
}
function ko(e, t, n) {
	let r = t.field(), i, a, o = e.uint32(), s = e.pos + o;
	for (; e.pos < s;) {
		let [t] = e.tag();
		switch (t) {
			case 1:
				i = Mo(e, r.mapKey);
				break;
			case 2: switch (r.mapKind) {
				case "scalar":
					a = Mo(e, r.scalar);
					break;
				case "enum":
					a = e.int32();
					break;
				case "message": a = jo(e, n, r);
			}
		}
	}
	if (i === void 0 && (i = vr(r.mapKey, !1)), a === void 0) switch (r.mapKind) {
		case "scalar":
			a = vr(r.scalar, !1);
			break;
		case "enum":
			a = r.enum.values[0].number;
			break;
		case "message": a = Ea(r.message, void 0, !1);
	}
	t.set(i, a);
}
function Ao(e, t, n, r) {
	let i = n.field();
	if (i.listKind === "message") {
		n.add(jo(e, r, i));
		return;
	}
	let a = i.scalar ?? v.INT32;
	if (t != b.LengthDelimited || a == v.STRING || a == v.BYTES) {
		n.add(Mo(e, a));
		return;
	}
	let o = e.uint32() + e.pos;
	for (; e.pos < o;) n.add(Mo(e, a));
}
function jo(e, t, n, r) {
	let i = n.delimitedEncoding, a = r ?? Ea(n.message, void 0, !1);
	return Do(a, e, t, i, i ? n.number : e.uint32()), a;
}
function Mo(e, t) {
	switch (t) {
		case v.STRING: return e.string();
		case v.BOOL: return e.bool();
		case v.DOUBLE: return e.double();
		case v.FLOAT: return e.float();
		case v.INT32: return e.int32();
		case v.INT64: return e.int64();
		case v.UINT64: return e.uint64();
		case v.FIXED64: return e.fixed64();
		case v.BYTES: return e.bytes();
		case v.FIXED32: return e.fixed32();
		case v.SFIXED32: return e.sfixed32();
		case v.SFIXED64: return e.sfixed64();
		case v.SINT64: return e.sint64();
		case v.UINT32: return e.uint32();
		case v.SINT32: return e.sint32();
	}
}
function T(e, t) {
	let n = w(ro, Di(e));
	return n.messageType.forEach(Or), n.dependency = t?.map((e) => e.proto.name) ?? [], Nr(n, (e) => t?.find((t) => t.proto.name === e)).getFile(n.name);
}
function E(e, t, ...n) {
	return n.reduce((e, t) => e.nestedMessages[t], e.messages[t]);
}
var D = Wn({
	ATAK: () => Kl,
	Admin: () => F,
	AppOnly: () => Ul,
	CannedMessages: () => iu,
	Channel: () => No,
	ClientOnly: () => du,
	Config: () => Vo,
	ConnectionStatus: () => ks,
	LocalOnly: () => su,
	Mesh: () => N,
	ModuleConfig: () => Ls,
	Mqtt: () => mu,
	PaxCount: () => vu,
	Portnums: () => A,
	PowerMon: () => xu,
	RemoteHardware: () => ku,
	Rtttl: () => Pu,
	StoreForward: () => Lu,
	Telemetry: () => hc,
	Xmodem: () => M
}), No = g({
	ChannelSchema: () => Lo,
	ChannelSettingsSchema: () => Fo,
	Channel_Role: () => Ro,
	Channel_RoleSchema: () => zo,
	ModuleSettingsSchema: () => Io,
	file_channel: () => Po
}), Po = /* @__PURE__ */ T("Cg1jaGFubmVsLnByb3RvEgptZXNodGFzdGljIrgBCg9DaGFubmVsU2V0dGluZ3MSFwoLY2hhbm5lbF9udW0YASABKA1CAhgBEgsKA3BzaxgCIAEoDBIMCgRuYW1lGAMgASgJEgoKAmlkGAQgASgHEhYKDnVwbGlua19lbmFibGVkGAUgASgIEhgKEGRvd25saW5rX2VuYWJsZWQYBiABKAgSMwoPbW9kdWxlX3NldHRpbmdzGAcgASgLMhoubWVzaHRhc3RpYy5Nb2R1bGVTZXR0aW5ncyJFCg5Nb2R1bGVTZXR0aW5ncxIaChJwb3NpdGlvbl9wcmVjaXNpb24YASABKA0SFwoPaXNfY2xpZW50X211dGVkGAIgASgIIqEBCgdDaGFubmVsEg0KBWluZGV4GAEgASgFEi0KCHNldHRpbmdzGAIgASgLMhsubWVzaHRhc3RpYy5DaGFubmVsU2V0dGluZ3MSJgoEcm9sZRgDIAEoDjIYLm1lc2h0YXN0aWMuQ2hhbm5lbC5Sb2xlIjAKBFJvbGUSDAoIRElTQUJMRUQQABILCgdQUklNQVJZEAESDQoJU0VDT05EQVJZEAJCYgoTY29tLmdlZWtzdmlsbGUubWVzaEINQ2hhbm5lbFByb3Rvc1oiZ2l0aHViLmNvbS9tZXNodGFzdGljL2dvL2dlbmVyYXRlZKoCFE1lc2h0YXN0aWMuUHJvdG9idWZzugIAYgZwcm90bzM"), Fo = /* @__PURE__ */ E(Po, 0), Io = /* @__PURE__ */ E(Po, 1), Lo = /* @__PURE__ */ E(Po, 2), Ro = /* @__PURE__ */ function(e) {
	return e[e.DISABLED = 0] = "DISABLED", e[e.PRIMARY = 1] = "PRIMARY", e[e.SECONDARY = 2] = "SECONDARY", e;
}({}), zo = /* @__PURE__ */ C(Po, 2, 0), Bo = /* @__PURE__ */ T("Cg9kZXZpY2VfdWkucHJvdG8SCm1lc2h0YXN0aWMivgMKDkRldmljZVVJQ29uZmlnEg8KB3ZlcnNpb24YASABKA0SGQoRc2NyZWVuX2JyaWdodG5lc3MYAiABKA0SFgoOc2NyZWVuX3RpbWVvdXQYAyABKA0SEwoLc2NyZWVuX2xvY2sYBCABKAgSFQoNc2V0dGluZ3NfbG9jaxgFIAEoCBIQCghwaW5fY29kZRgGIAEoDRIgCgV0aGVtZRgHIAEoDjIRLm1lc2h0YXN0aWMuVGhlbWUSFQoNYWxlcnRfZW5hYmxlZBgIIAEoCBIWCg5iYW5uZXJfZW5hYmxlZBgJIAEoCBIUCgxyaW5nX3RvbmVfaWQYCiABKA0SJgoIbGFuZ3VhZ2UYCyABKA4yFC5tZXNodGFzdGljLkxhbmd1YWdlEisKC25vZGVfZmlsdGVyGAwgASgLMhYubWVzaHRhc3RpYy5Ob2RlRmlsdGVyEjEKDm5vZGVfaGlnaGxpZ2h0GA0gASgLMhkubWVzaHRhc3RpYy5Ob2RlSGlnaGxpZ2h0EhgKEGNhbGlicmF0aW9uX2RhdGEYDiABKAwSIQoIbWFwX2RhdGEYDyABKAsyDy5tZXNodGFzdGljLk1hcCKnAQoKTm9kZUZpbHRlchIWCg51bmtub3duX3N3aXRjaBgBIAEoCBIWCg5vZmZsaW5lX3N3aXRjaBgCIAEoCBIZChFwdWJsaWNfa2V5X3N3aXRjaBgDIAEoCBIRCglob3BzX2F3YXkYBCABKAUSFwoPcG9zaXRpb25fc3dpdGNoGAUgASgIEhEKCW5vZGVfbmFtZRgGIAEoCRIPCgdjaGFubmVsGAcgASgFIn4KDU5vZGVIaWdobGlnaHQSEwoLY2hhdF9zd2l0Y2gYASABKAgSFwoPcG9zaXRpb25fc3dpdGNoGAIgASgIEhgKEHRlbGVtZXRyeV9zd2l0Y2gYAyABKAgSEgoKaWFxX3N3aXRjaBgEIAEoCBIRCglub2RlX25hbWUYBSABKAkiPQoIR2VvUG9pbnQSDAoEem9vbRgBIAEoBRIQCghsYXRpdHVkZRgCIAEoBRIRCglsb25naXR1ZGUYAyABKAUiTAoDTWFwEiIKBGhvbWUYASABKAsyFC5tZXNodGFzdGljLkdlb1BvaW50Eg0KBXN0eWxlGAIgASgJEhIKCmZvbGxvd19ncHMYAyABKAgqJQoFVGhlbWUSCAoEREFSSxAAEgkKBUxJR0hUEAESBwoDUkVEEAIqqQIKCExhbmd1YWdlEgsKB0VOR0xJU0gQABIKCgZGUkVOQ0gQARIKCgZHRVJNQU4QAhILCgdJVEFMSUFOEAMSDgoKUE9SVFVHVUVTRRAEEgsKB1NQQU5JU0gQBRILCgdTV0VESVNIEAYSCwoHRklOTklTSBAHEgoKBlBPTElTSBAIEgsKB1RVUktJU0gQCRILCgdTRVJCSUFOEAoSCwoHUlVTU0lBThALEgkKBURVVENIEAwSCQoFR1JFRUsQDRINCglOT1JXRUdJQU4QDhINCglTTE9WRU5JQU4QDxINCglVS1JBSU5JQU4QEBINCglCVUxHQVJJQU4QERIWChJTSU1QTElGSUVEX0NISU5FU0UQHhIXChNUUkFESVRJT05BTF9DSElORVNFEB9CYwoTY29tLmdlZWtzdmlsbGUubWVzaEIORGV2aWNlVUlQcm90b3NaImdpdGh1Yi5jb20vbWVzaHRhc3RpYy9nby9nZW5lcmF0ZWSqAhRNZXNodGFzdGljLlByb3RvYnVmc7oCAGIGcHJvdG8z"), Vo = g({
	ConfigSchema: () => Ho,
	Config_BluetoothConfigSchema: () => ws,
	Config_BluetoothConfig_PairingMode: () => Ts,
	Config_BluetoothConfig_PairingModeSchema: () => Es,
	Config_DeviceConfigSchema: () => Uo,
	Config_DeviceConfig_BuzzerMode: () => Jo,
	Config_DeviceConfig_BuzzerModeSchema: () => Yo,
	Config_DeviceConfig_RebroadcastMode: () => Ko,
	Config_DeviceConfig_RebroadcastModeSchema: () => qo,
	Config_DeviceConfig_Role: () => Wo,
	Config_DeviceConfig_RoleSchema: () => Go,
	Config_DisplayConfigSchema: () => cs,
	Config_DisplayConfig_CompassOrientation: () => _s,
	Config_DisplayConfig_CompassOrientationSchema: () => vs,
	Config_DisplayConfig_DisplayMode: () => hs,
	Config_DisplayConfig_DisplayModeSchema: () => gs,
	Config_DisplayConfig_DisplayUnits: () => ds,
	Config_DisplayConfig_DisplayUnitsSchema: () => fs,
	Config_DisplayConfig_GpsCoordinateFormat: () => ls,
	Config_DisplayConfig_GpsCoordinateFormatSchema: () => us,
	Config_DisplayConfig_OledType: () => ps,
	Config_DisplayConfig_OledTypeSchema: () => ms,
	Config_LoRaConfigSchema: () => ys,
	Config_LoRaConfig_ModemPreset: () => Ss,
	Config_LoRaConfig_ModemPresetSchema: () => Cs,
	Config_LoRaConfig_RegionCode: () => bs,
	Config_LoRaConfig_RegionCodeSchema: () => xs,
	Config_NetworkConfigSchema: () => ns,
	Config_NetworkConfig_AddressMode: () => is,
	Config_NetworkConfig_AddressModeSchema: () => as,
	Config_NetworkConfig_IpV4ConfigSchema: () => rs,
	Config_NetworkConfig_ProtocolFlags: () => os,
	Config_NetworkConfig_ProtocolFlagsSchema: () => ss,
	Config_PositionConfigSchema: () => Xo,
	Config_PositionConfig_GpsMode: () => $o,
	Config_PositionConfig_GpsModeSchema: () => es,
	Config_PositionConfig_PositionFlags: () => Zo,
	Config_PositionConfig_PositionFlagsSchema: () => Qo,
	Config_PowerConfigSchema: () => ts,
	Config_SecurityConfigSchema: () => Ds,
	Config_SessionkeyConfigSchema: () => Os,
	file_config: () => O
}), O = /* @__PURE__ */ T("Cgxjb25maWcucHJvdG8SCm1lc2h0YXN0aWMipigKBkNvbmZpZxIxCgZkZXZpY2UYASABKAsyHy5tZXNodGFzdGljLkNvbmZpZy5EZXZpY2VDb25maWdIABI1Cghwb3NpdGlvbhgCIAEoCzIhLm1lc2h0YXN0aWMuQ29uZmlnLlBvc2l0aW9uQ29uZmlnSAASLwoFcG93ZXIYAyABKAsyHi5tZXNodGFzdGljLkNvbmZpZy5Qb3dlckNvbmZpZ0gAEjMKB25ldHdvcmsYBCABKAsyIC5tZXNodGFzdGljLkNvbmZpZy5OZXR3b3JrQ29uZmlnSAASMwoHZGlzcGxheRgFIAEoCzIgLm1lc2h0YXN0aWMuQ29uZmlnLkRpc3BsYXlDb25maWdIABItCgRsb3JhGAYgASgLMh0ubWVzaHRhc3RpYy5Db25maWcuTG9SYUNvbmZpZ0gAEjcKCWJsdWV0b290aBgHIAEoCzIiLm1lc2h0YXN0aWMuQ29uZmlnLkJsdWV0b290aENvbmZpZ0gAEjUKCHNlY3VyaXR5GAggASgLMiEubWVzaHRhc3RpYy5Db25maWcuU2VjdXJpdHlDb25maWdIABI5CgpzZXNzaW9ua2V5GAkgASgLMiMubWVzaHRhc3RpYy5Db25maWcuU2Vzc2lvbmtleUNvbmZpZ0gAEi8KCWRldmljZV91aRgKIAEoCzIaLm1lc2h0YXN0aWMuRGV2aWNlVUlDb25maWdIABrMBgoMRGV2aWNlQ29uZmlnEjIKBHJvbGUYASABKA4yJC5tZXNodGFzdGljLkNvbmZpZy5EZXZpY2VDb25maWcuUm9sZRIaCg5zZXJpYWxfZW5hYmxlZBgCIAEoCEICGAESEwoLYnV0dG9uX2dwaW8YBCABKA0SEwoLYnV6emVyX2dwaW8YBSABKA0SSQoQcmVicm9hZGNhc3RfbW9kZRgGIAEoDjIvLm1lc2h0YXN0aWMuQ29uZmlnLkRldmljZUNvbmZpZy5SZWJyb2FkY2FzdE1vZGUSIAoYbm9kZV9pbmZvX2Jyb2FkY2FzdF9zZWNzGAcgASgNEiIKGmRvdWJsZV90YXBfYXNfYnV0dG9uX3ByZXNzGAggASgIEhYKCmlzX21hbmFnZWQYCSABKAhCAhgBEhwKFGRpc2FibGVfdHJpcGxlX2NsaWNrGAogASgIEg0KBXR6ZGVmGAsgASgJEh4KFmxlZF9oZWFydGJlYXRfZGlzYWJsZWQYDCABKAgSPwoLYnV6emVyX21vZGUYDSABKA4yKi5tZXNodGFzdGljLkNvbmZpZy5EZXZpY2VDb25maWcuQnV6emVyTW9kZSK/AQoEUm9sZRIKCgZDTElFTlQQABIPCgtDTElFTlRfTVVURRABEgoKBlJPVVRFUhACEhUKDVJPVVRFUl9DTElFTlQQAxoCCAESDAoIUkVQRUFURVIQBBILCgdUUkFDS0VSEAUSCgoGU0VOU09SEAYSBwoDVEFLEAcSEQoNQ0xJRU5UX0hJRERFThAIEhIKDkxPU1RfQU5EX0ZPVU5EEAkSDwoLVEFLX1RSQUNLRVIQChIPCgtST1VURVJfTEFURRALInMKD1JlYnJvYWRjYXN0TW9kZRIHCgNBTEwQABIVChFBTExfU0tJUF9ERUNPRElORxABEg4KCkxPQ0FMX09OTFkQAhIOCgpLTk9XTl9PTkxZEAMSCAoETk9ORRAEEhYKEkNPUkVfUE9SVE5VTVNfT05MWRAFIlQKCkJ1enplck1vZGUSDwoLQUxMX0VOQUJMRUQQABIMCghESVNBQkxFRBABEhYKEk5PVElGSUNBVElPTlNfT05MWRACEg8KC1NZU1RFTV9PTkxZEAMakQUKDlBvc2l0aW9uQ29uZmlnEh8KF3Bvc2l0aW9uX2Jyb2FkY2FzdF9zZWNzGAEgASgNEigKIHBvc2l0aW9uX2Jyb2FkY2FzdF9zbWFydF9lbmFibGVkGAIgASgIEhYKDmZpeGVkX3Bvc2l0aW9uGAMgASgIEhcKC2dwc19lbmFibGVkGAQgASgIQgIYARIbChNncHNfdXBkYXRlX2ludGVydmFsGAUgASgNEhwKEGdwc19hdHRlbXB0X3RpbWUYBiABKA1CAhgBEhYKDnBvc2l0aW9uX2ZsYWdzGAcgASgNEg8KB3J4X2dwaW8YCCABKA0SDwoHdHhfZ3BpbxgJIAEoDRIoCiBicm9hZGNhc3Rfc21hcnRfbWluaW11bV9kaXN0YW5jZRgKIAEoDRItCiVicm9hZGNhc3Rfc21hcnRfbWluaW11bV9pbnRlcnZhbF9zZWNzGAsgASgNEhMKC2dwc19lbl9ncGlvGAwgASgNEjsKCGdwc19tb2RlGA0gASgOMikubWVzaHRhc3RpYy5Db25maWcuUG9zaXRpb25Db25maWcuR3BzTW9kZSKrAQoNUG9zaXRpb25GbGFncxIJCgVVTlNFVBAAEgwKCEFMVElUVURFEAESEAoMQUxUSVRVREVfTVNMEAISFgoSR0VPSURBTF9TRVBBUkFUSU9OEAQSBwoDRE9QEAgSCQoFSFZET1AQEBINCglTQVRJTlZJRVcQIBIKCgZTRVFfTk8QQBIOCglUSU1FU1RBTVAQgAESDAoHSEVBRElORxCAAhIKCgVTUEVFRBCABCI1CgdHcHNNb2RlEgwKCERJU0FCTEVEEAASCwoHRU5BQkxFRBABEg8KC05PVF9QUkVTRU5UEAIahAIKC1Bvd2VyQ29uZmlnEhcKD2lzX3Bvd2VyX3NhdmluZxgBIAEoCBImCh5vbl9iYXR0ZXJ5X3NodXRkb3duX2FmdGVyX3NlY3MYAiABKA0SHwoXYWRjX211bHRpcGxpZXJfb3ZlcnJpZGUYAyABKAISGwoTd2FpdF9ibHVldG9vdGhfc2VjcxgEIAEoDRIQCghzZHNfc2VjcxgGIAEoDRIPCgdsc19zZWNzGAcgASgNEhUKDW1pbl93YWtlX3NlY3MYCCABKA0SIgoaZGV2aWNlX2JhdHRlcnlfaW5hX2FkZHJlc3MYCSABKA0SGAoQcG93ZXJtb25fZW5hYmxlcxggIAEoBBrlAwoNTmV0d29ya0NvbmZpZxIUCgx3aWZpX2VuYWJsZWQYASABKAgSEQoJd2lmaV9zc2lkGAMgASgJEhAKCHdpZmlfcHNrGAQgASgJEhIKCm50cF9zZXJ2ZXIYBSABKAkSEwoLZXRoX2VuYWJsZWQYBiABKAgSQgoMYWRkcmVzc19tb2RlGAcgASgOMiwubWVzaHRhc3RpYy5Db25maWcuTmV0d29ya0NvbmZpZy5BZGRyZXNzTW9kZRJACgtpcHY0X2NvbmZpZxgIIAEoCzIrLm1lc2h0YXN0aWMuQ29uZmlnLk5ldHdvcmtDb25maWcuSXBWNENvbmZpZxIWCg5yc3lzbG9nX3NlcnZlchgJIAEoCRIZChFlbmFibGVkX3Byb3RvY29scxgKIAEoDRIUCgxpcHY2X2VuYWJsZWQYCyABKAgaRgoKSXBWNENvbmZpZxIKCgJpcBgBIAEoBxIPCgdnYXRld2F5GAIgASgHEg4KBnN1Ym5ldBgDIAEoBxILCgNkbnMYBCABKAciIwoLQWRkcmVzc01vZGUSCAoEREhDUBAAEgoKBlNUQVRJQxABIjQKDVByb3RvY29sRmxhZ3MSEAoMTk9fQlJPQURDQVNUEAASEQoNVURQX0JST0FEQ0FTVBABGvwHCg1EaXNwbGF5Q29uZmlnEhYKDnNjcmVlbl9vbl9zZWNzGAEgASgNEkgKCmdwc19mb3JtYXQYAiABKA4yNC5tZXNodGFzdGljLkNvbmZpZy5EaXNwbGF5Q29uZmlnLkdwc0Nvb3JkaW5hdGVGb3JtYXQSIQoZYXV0b19zY3JlZW5fY2Fyb3VzZWxfc2VjcxgDIAEoDRIZChFjb21wYXNzX25vcnRoX3RvcBgEIAEoCBITCgtmbGlwX3NjcmVlbhgFIAEoCBI8CgV1bml0cxgGIAEoDjItLm1lc2h0YXN0aWMuQ29uZmlnLkRpc3BsYXlDb25maWcuRGlzcGxheVVuaXRzEjcKBG9sZWQYByABKA4yKS5tZXNodGFzdGljLkNvbmZpZy5EaXNwbGF5Q29uZmlnLk9sZWRUeXBlEkEKC2Rpc3BsYXltb2RlGAggASgOMiwubWVzaHRhc3RpYy5Db25maWcuRGlzcGxheUNvbmZpZy5EaXNwbGF5TW9kZRIUCgxoZWFkaW5nX2JvbGQYCSABKAgSHQoVd2FrZV9vbl90YXBfb3JfbW90aW9uGAogASgIElAKE2NvbXBhc3Nfb3JpZW50YXRpb24YCyABKA4yMy5tZXNodGFzdGljLkNvbmZpZy5EaXNwbGF5Q29uZmlnLkNvbXBhc3NPcmllbnRhdGlvbhIVCg11c2VfMTJoX2Nsb2NrGAwgASgIIk0KE0dwc0Nvb3JkaW5hdGVGb3JtYXQSBwoDREVDEAASBwoDRE1TEAESBwoDVVRNEAISCAoETUdSUxADEgcKA09MQxAEEggKBE9TR1IQBSIoCgxEaXNwbGF5VW5pdHMSCgoGTUVUUklDEAASDAoISU1QRVJJQUwQASJlCghPbGVkVHlwZRINCglPTEVEX0FVVE8QABIQCgxPTEVEX1NTRDEzMDYQARIPCgtPTEVEX1NIMTEwNhACEg8KC09MRURfU0gxMTA3EAMSFgoST0xFRF9TSDExMDdfMTI4XzY0EAQiQQoLRGlzcGxheU1vZGUSCwoHREVGQVVMVBAAEgwKCFRXT0NPTE9SEAESDAoISU5WRVJURUQQAhIJCgVDT0xPUhADIroBChJDb21wYXNzT3JpZW50YXRpb24SDQoJREVHUkVFU18wEAASDgoKREVHUkVFU185MBABEg8KC0RFR1JFRVNfMTgwEAISDwoLREVHUkVFU18yNzAQAxIWChJERUdSRUVTXzBfSU5WRVJURUQQBBIXChNERUdSRUVTXzkwX0lOVkVSVEVEEAUSGAoUREVHUkVFU18xODBfSU5WRVJURUQQBhIYChRERUdSRUVTXzI3MF9JTlZFUlRFRBAHGqoHCgpMb1JhQ29uZmlnEhIKCnVzZV9wcmVzZXQYASABKAgSPwoMbW9kZW1fcHJlc2V0GAIgASgOMikubWVzaHRhc3RpYy5Db25maWcuTG9SYUNvbmZpZy5Nb2RlbVByZXNldBIRCgliYW5kd2lkdGgYAyABKA0SFQoNc3ByZWFkX2ZhY3RvchgEIAEoDRITCgtjb2RpbmdfcmF0ZRgFIAEoDRIYChBmcmVxdWVuY3lfb2Zmc2V0GAYgASgCEjgKBnJlZ2lvbhgHIAEoDjIoLm1lc2h0YXN0aWMuQ29uZmlnLkxvUmFDb25maWcuUmVnaW9uQ29kZRIRCglob3BfbGltaXQYCCABKA0SEgoKdHhfZW5hYmxlZBgJIAEoCBIQCgh0eF9wb3dlchgKIAEoBRITCgtjaGFubmVsX251bRgLIAEoDRIbChNvdmVycmlkZV9kdXR5X2N5Y2xlGAwgASgIEh4KFnN4MTI2eF9yeF9ib29zdGVkX2dhaW4YDSABKAgSGgoSb3ZlcnJpZGVfZnJlcXVlbmN5GA4gASgCEhcKD3BhX2Zhbl9kaXNhYmxlZBgPIAEoCBIXCg9pZ25vcmVfaW5jb21pbmcYZyADKA0SEwoLaWdub3JlX21xdHQYaCABKAgSGQoRY29uZmlnX29rX3RvX21xdHQYaSABKAgi/gEKClJlZ2lvbkNvZGUSCQoFVU5TRVQQABIGCgJVUxABEgoKBkVVXzQzMxACEgoKBkVVXzg2OBADEgYKAkNOEAQSBgoCSlAQBRIHCgNBTloQBhIGCgJLUhAHEgYKAlRXEAgSBgoCUlUQCRIGCgJJThAKEgoKBk5aXzg2NRALEgYKAlRIEAwSCwoHTE9SQV8yNBANEgoKBlVBXzQzMxAOEgoKBlVBXzg2OBAPEgoKBk1ZXzQzMxAQEgoKBk1ZXzkxORAREgoKBlNHXzkyMxASEgoKBlBIXzQzMxATEgoKBlBIXzg2OBAUEgoKBlBIXzkxNRAVEgsKB0FOWl80MzMQFiKpAQoLTW9kZW1QcmVzZXQSDQoJTE9OR19GQVNUEAASDQoJTE9OR19TTE9XEAESFgoOVkVSWV9MT05HX1NMT1cQAhoCCAESDwoLTUVESVVNX1NMT1cQAxIPCgtNRURJVU1fRkFTVBAEEg4KClNIT1JUX1NMT1cQBRIOCgpTSE9SVF9GQVNUEAYSEQoNTE9OR19NT0RFUkFURRAHEg8KC1NIT1JUX1RVUkJPEAgarQEKD0JsdWV0b290aENvbmZpZxIPCgdlbmFibGVkGAEgASgIEjwKBG1vZGUYAiABKA4yLi5tZXNodGFzdGljLkNvbmZpZy5CbHVldG9vdGhDb25maWcuUGFpcmluZ01vZGUSEQoJZml4ZWRfcGluGAMgASgNIjgKC1BhaXJpbmdNb2RlEg4KClJBTkRPTV9QSU4QABINCglGSVhFRF9QSU4QARIKCgZOT19QSU4QAhq2AQoOU2VjdXJpdHlDb25maWcSEgoKcHVibGljX2tleRgBIAEoDBITCgtwcml2YXRlX2tleRgCIAEoDBIRCglhZG1pbl9rZXkYAyADKAwSEgoKaXNfbWFuYWdlZBgEIAEoCBIWCg5zZXJpYWxfZW5hYmxlZBgFIAEoCBIdChVkZWJ1Z19sb2dfYXBpX2VuYWJsZWQYBiABKAgSHQoVYWRtaW5fY2hhbm5lbF9lbmFibGVkGAggASgIGhIKEFNlc3Npb25rZXlDb25maWdCEQoPcGF5bG9hZF92YXJpYW50QmEKE2NvbS5nZWVrc3ZpbGxlLm1lc2hCDENvbmZpZ1Byb3Rvc1oiZ2l0aHViLmNvbS9tZXNodGFzdGljL2dvL2dlbmVyYXRlZKoCFE1lc2h0YXN0aWMuUHJvdG9idWZzugIAYgZwcm90bzM", [Bo]), Ho = /* @__PURE__ */ E(O, 0), Uo = /* @__PURE__ */ E(O, 0, 0), Wo = /* @__PURE__ */ function(e) {
	return e[e.CLIENT = 0] = "CLIENT", e[e.CLIENT_MUTE = 1] = "CLIENT_MUTE", e[e.ROUTER = 2] = "ROUTER", e[e.ROUTER_CLIENT = 3] = "ROUTER_CLIENT", e[e.REPEATER = 4] = "REPEATER", e[e.TRACKER = 5] = "TRACKER", e[e.SENSOR = 6] = "SENSOR", e[e.TAK = 7] = "TAK", e[e.CLIENT_HIDDEN = 8] = "CLIENT_HIDDEN", e[e.LOST_AND_FOUND = 9] = "LOST_AND_FOUND", e[e.TAK_TRACKER = 10] = "TAK_TRACKER", e[e.ROUTER_LATE = 11] = "ROUTER_LATE", e;
}({}), Go = /* @__PURE__ */ C(O, 0, 0, 0), Ko = /* @__PURE__ */ function(e) {
	return e[e.ALL = 0] = "ALL", e[e.ALL_SKIP_DECODING = 1] = "ALL_SKIP_DECODING", e[e.LOCAL_ONLY = 2] = "LOCAL_ONLY", e[e.KNOWN_ONLY = 3] = "KNOWN_ONLY", e[e.NONE = 4] = "NONE", e[e.CORE_PORTNUMS_ONLY = 5] = "CORE_PORTNUMS_ONLY", e;
}({}), qo = /* @__PURE__ */ C(O, 0, 0, 1), Jo = /* @__PURE__ */ function(e) {
	return e[e.ALL_ENABLED = 0] = "ALL_ENABLED", e[e.DISABLED = 1] = "DISABLED", e[e.NOTIFICATIONS_ONLY = 2] = "NOTIFICATIONS_ONLY", e[e.SYSTEM_ONLY = 3] = "SYSTEM_ONLY", e;
}({}), Yo = /* @__PURE__ */ C(O, 0, 0, 2), Xo = /* @__PURE__ */ E(O, 0, 1), Zo = /* @__PURE__ */ function(e) {
	return e[e.UNSET = 0] = "UNSET", e[e.ALTITUDE = 1] = "ALTITUDE", e[e.ALTITUDE_MSL = 2] = "ALTITUDE_MSL", e[e.GEOIDAL_SEPARATION = 4] = "GEOIDAL_SEPARATION", e[e.DOP = 8] = "DOP", e[e.HVDOP = 16] = "HVDOP", e[e.SATINVIEW = 32] = "SATINVIEW", e[e.SEQ_NO = 64] = "SEQ_NO", e[e.TIMESTAMP = 128] = "TIMESTAMP", e[e.HEADING = 256] = "HEADING", e[e.SPEED = 512] = "SPEED", e;
}({}), Qo = /* @__PURE__ */ C(O, 0, 1, 0), $o = /* @__PURE__ */ function(e) {
	return e[e.DISABLED = 0] = "DISABLED", e[e.ENABLED = 1] = "ENABLED", e[e.NOT_PRESENT = 2] = "NOT_PRESENT", e;
}({}), es = /* @__PURE__ */ C(O, 0, 1, 1), ts = /* @__PURE__ */ E(O, 0, 2), ns = /* @__PURE__ */ E(O, 0, 3), rs = /* @__PURE__ */ E(O, 0, 3, 0), is = /* @__PURE__ */ function(e) {
	return e[e.DHCP = 0] = "DHCP", e[e.STATIC = 1] = "STATIC", e;
}({}), as = /* @__PURE__ */ C(O, 0, 3, 0), os = /* @__PURE__ */ function(e) {
	return e[e.NO_BROADCAST = 0] = "NO_BROADCAST", e[e.UDP_BROADCAST = 1] = "UDP_BROADCAST", e;
}({}), ss = /* @__PURE__ */ C(O, 0, 3, 1), cs = /* @__PURE__ */ E(O, 0, 4), ls = /* @__PURE__ */ function(e) {
	return e[e.DEC = 0] = "DEC", e[e.DMS = 1] = "DMS", e[e.UTM = 2] = "UTM", e[e.MGRS = 3] = "MGRS", e[e.OLC = 4] = "OLC", e[e.OSGR = 5] = "OSGR", e;
}({}), us = /* @__PURE__ */ C(O, 0, 4, 0), ds = /* @__PURE__ */ function(e) {
	return e[e.METRIC = 0] = "METRIC", e[e.IMPERIAL = 1] = "IMPERIAL", e;
}({}), fs = /* @__PURE__ */ C(O, 0, 4, 1), ps = /* @__PURE__ */ function(e) {
	return e[e.OLED_AUTO = 0] = "OLED_AUTO", e[e.OLED_SSD1306 = 1] = "OLED_SSD1306", e[e.OLED_SH1106 = 2] = "OLED_SH1106", e[e.OLED_SH1107 = 3] = "OLED_SH1107", e[e.OLED_SH1107_128_64 = 4] = "OLED_SH1107_128_64", e;
}({}), ms = /* @__PURE__ */ C(O, 0, 4, 2), hs = /* @__PURE__ */ function(e) {
	return e[e.DEFAULT = 0] = "DEFAULT", e[e.TWOCOLOR = 1] = "TWOCOLOR", e[e.INVERTED = 2] = "INVERTED", e[e.COLOR = 3] = "COLOR", e;
}({}), gs = /* @__PURE__ */ C(O, 0, 4, 3), _s = /* @__PURE__ */ function(e) {
	return e[e.DEGREES_0 = 0] = "DEGREES_0", e[e.DEGREES_90 = 1] = "DEGREES_90", e[e.DEGREES_180 = 2] = "DEGREES_180", e[e.DEGREES_270 = 3] = "DEGREES_270", e[e.DEGREES_0_INVERTED = 4] = "DEGREES_0_INVERTED", e[e.DEGREES_90_INVERTED = 5] = "DEGREES_90_INVERTED", e[e.DEGREES_180_INVERTED = 6] = "DEGREES_180_INVERTED", e[e.DEGREES_270_INVERTED = 7] = "DEGREES_270_INVERTED", e;
}({}), vs = /* @__PURE__ */ C(O, 0, 4, 4), ys = /* @__PURE__ */ E(O, 0, 5), bs = /* @__PURE__ */ function(e) {
	return e[e.UNSET = 0] = "UNSET", e[e.US = 1] = "US", e[e.EU_433 = 2] = "EU_433", e[e.EU_868 = 3] = "EU_868", e[e.CN = 4] = "CN", e[e.JP = 5] = "JP", e[e.ANZ = 6] = "ANZ", e[e.KR = 7] = "KR", e[e.TW = 8] = "TW", e[e.RU = 9] = "RU", e[e.IN = 10] = "IN", e[e.NZ_865 = 11] = "NZ_865", e[e.TH = 12] = "TH", e[e.LORA_24 = 13] = "LORA_24", e[e.UA_433 = 14] = "UA_433", e[e.UA_868 = 15] = "UA_868", e[e.MY_433 = 16] = "MY_433", e[e.MY_919 = 17] = "MY_919", e[e.SG_923 = 18] = "SG_923", e[e.PH_433 = 19] = "PH_433", e[e.PH_868 = 20] = "PH_868", e[e.PH_915 = 21] = "PH_915", e[e.ANZ_433 = 22] = "ANZ_433", e;
}({}), xs = /* @__PURE__ */ C(O, 0, 5, 0), Ss = /* @__PURE__ */ function(e) {
	return e[e.LONG_FAST = 0] = "LONG_FAST", e[e.LONG_SLOW = 1] = "LONG_SLOW", e[e.VERY_LONG_SLOW = 2] = "VERY_LONG_SLOW", e[e.MEDIUM_SLOW = 3] = "MEDIUM_SLOW", e[e.MEDIUM_FAST = 4] = "MEDIUM_FAST", e[e.SHORT_SLOW = 5] = "SHORT_SLOW", e[e.SHORT_FAST = 6] = "SHORT_FAST", e[e.LONG_MODERATE = 7] = "LONG_MODERATE", e[e.SHORT_TURBO = 8] = "SHORT_TURBO", e;
}({}), Cs = /* @__PURE__ */ C(O, 0, 5, 1), ws = /* @__PURE__ */ E(O, 0, 6), Ts = /* @__PURE__ */ function(e) {
	return e[e.RANDOM_PIN = 0] = "RANDOM_PIN", e[e.FIXED_PIN = 1] = "FIXED_PIN", e[e.NO_PIN = 2] = "NO_PIN", e;
}({}), Es = /* @__PURE__ */ C(O, 0, 6, 0), Ds = /* @__PURE__ */ E(O, 0, 7), Os = /* @__PURE__ */ E(O, 0, 8), ks = g({
	BluetoothConnectionStatusSchema: () => Fs,
	DeviceConnectionStatusSchema: () => js,
	EthernetConnectionStatusSchema: () => Ns,
	NetworkConnectionStatusSchema: () => Ps,
	SerialConnectionStatusSchema: () => Is,
	WifiConnectionStatusSchema: () => Ms,
	file_connection_status: () => As
}), As = /* @__PURE__ */ T("Chdjb25uZWN0aW9uX3N0YXR1cy5wcm90bxIKbWVzaHRhc3RpYyKxAgoWRGV2aWNlQ29ubmVjdGlvblN0YXR1cxIzCgR3aWZpGAEgASgLMiAubWVzaHRhc3RpYy5XaWZpQ29ubmVjdGlvblN0YXR1c0gAiAEBEjsKCGV0aGVybmV0GAIgASgLMiQubWVzaHRhc3RpYy5FdGhlcm5ldENvbm5lY3Rpb25TdGF0dXNIAYgBARI9CglibHVldG9vdGgYAyABKAsyJS5tZXNodGFzdGljLkJsdWV0b290aENvbm5lY3Rpb25TdGF0dXNIAogBARI3CgZzZXJpYWwYBCABKAsyIi5tZXNodGFzdGljLlNlcmlhbENvbm5lY3Rpb25TdGF0dXNIA4gBAUIHCgVfd2lmaUILCglfZXRoZXJuZXRCDAoKX2JsdWV0b290aEIJCgdfc2VyaWFsImcKFFdpZmlDb25uZWN0aW9uU3RhdHVzEjMKBnN0YXR1cxgBIAEoCzIjLm1lc2h0YXN0aWMuTmV0d29ya0Nvbm5lY3Rpb25TdGF0dXMSDAoEc3NpZBgCIAEoCRIMCgRyc3NpGAMgASgFIk8KGEV0aGVybmV0Q29ubmVjdGlvblN0YXR1cxIzCgZzdGF0dXMYASABKAsyIy5tZXNodGFzdGljLk5ldHdvcmtDb25uZWN0aW9uU3RhdHVzInsKF05ldHdvcmtDb25uZWN0aW9uU3RhdHVzEhIKCmlwX2FkZHJlc3MYASABKAcSFAoMaXNfY29ubmVjdGVkGAIgASgIEhkKEWlzX21xdHRfY29ubmVjdGVkGAMgASgIEhsKE2lzX3N5c2xvZ19jb25uZWN0ZWQYBCABKAgiTAoZQmx1ZXRvb3RoQ29ubmVjdGlvblN0YXR1cxILCgNwaW4YASABKA0SDAoEcnNzaRgCIAEoBRIUCgxpc19jb25uZWN0ZWQYAyABKAgiPAoWU2VyaWFsQ29ubmVjdGlvblN0YXR1cxIMCgRiYXVkGAEgASgNEhQKDGlzX2Nvbm5lY3RlZBgCIAEoCEJlChNjb20uZ2Vla3N2aWxsZS5tZXNoQhBDb25uU3RhdHVzUHJvdG9zWiJnaXRodWIuY29tL21lc2h0YXN0aWMvZ28vZ2VuZXJhdGVkqgIUTWVzaHRhc3RpYy5Qcm90b2J1ZnO6AgBiBnByb3RvMw"), js = /* @__PURE__ */ E(As, 0), Ms = /* @__PURE__ */ E(As, 1), Ns = /* @__PURE__ */ E(As, 2), Ps = /* @__PURE__ */ E(As, 3), Fs = /* @__PURE__ */ E(As, 4), Is = /* @__PURE__ */ E(As, 5), Ls = g({
	ModuleConfigSchema: () => Rs,
	ModuleConfig_AmbientLightingConfigSchema: () => cc,
	ModuleConfig_AudioConfigSchema: () => Ks,
	ModuleConfig_AudioConfig_Audio_Baud: () => qs,
	ModuleConfig_AudioConfig_Audio_BaudSchema: () => Js,
	ModuleConfig_CannedMessageConfigSchema: () => ac,
	ModuleConfig_CannedMessageConfig_InputEventChar: () => oc,
	ModuleConfig_CannedMessageConfig_InputEventCharSchema: () => sc,
	ModuleConfig_DetectionSensorConfigSchema: () => Us,
	ModuleConfig_DetectionSensorConfig_TriggerType: () => Ws,
	ModuleConfig_DetectionSensorConfig_TriggerTypeSchema: () => Gs,
	ModuleConfig_ExternalNotificationConfigSchema: () => tc,
	ModuleConfig_MQTTConfigSchema: () => zs,
	ModuleConfig_MapReportSettingsSchema: () => Bs,
	ModuleConfig_NeighborInfoConfigSchema: () => Hs,
	ModuleConfig_PaxcounterConfigSchema: () => Ys,
	ModuleConfig_RangeTestConfigSchema: () => rc,
	ModuleConfig_RemoteHardwareConfigSchema: () => Vs,
	ModuleConfig_SerialConfigSchema: () => Xs,
	ModuleConfig_SerialConfig_Serial_Baud: () => Zs,
	ModuleConfig_SerialConfig_Serial_BaudSchema: () => Qs,
	ModuleConfig_SerialConfig_Serial_Mode: () => $s,
	ModuleConfig_SerialConfig_Serial_ModeSchema: () => ec,
	ModuleConfig_StoreForwardConfigSchema: () => nc,
	ModuleConfig_TelemetryConfigSchema: () => ic,
	RemoteHardwarePinSchema: () => lc,
	RemoteHardwarePinType: () => uc,
	RemoteHardwarePinTypeSchema: () => dc,
	file_module_config: () => k
}), k = /* @__PURE__ */ T("ChNtb2R1bGVfY29uZmlnLnByb3RvEgptZXNodGFzdGljIuMlCgxNb2R1bGVDb25maWcSMwoEbXF0dBgBIAEoCzIjLm1lc2h0YXN0aWMuTW9kdWxlQ29uZmlnLk1RVFRDb25maWdIABI3CgZzZXJpYWwYAiABKAsyJS5tZXNodGFzdGljLk1vZHVsZUNvbmZpZy5TZXJpYWxDb25maWdIABJUChVleHRlcm5hbF9ub3RpZmljYXRpb24YAyABKAsyMy5tZXNodGFzdGljLk1vZHVsZUNvbmZpZy5FeHRlcm5hbE5vdGlmaWNhdGlvbkNvbmZpZ0gAEkQKDXN0b3JlX2ZvcndhcmQYBCABKAsyKy5tZXNodGFzdGljLk1vZHVsZUNvbmZpZy5TdG9yZUZvcndhcmRDb25maWdIABI+CgpyYW5nZV90ZXN0GAUgASgLMigubWVzaHRhc3RpYy5Nb2R1bGVDb25maWcuUmFuZ2VUZXN0Q29uZmlnSAASPQoJdGVsZW1ldHJ5GAYgASgLMigubWVzaHRhc3RpYy5Nb2R1bGVDb25maWcuVGVsZW1ldHJ5Q29uZmlnSAASRgoOY2FubmVkX21lc3NhZ2UYByABKAsyLC5tZXNodGFzdGljLk1vZHVsZUNvbmZpZy5DYW5uZWRNZXNzYWdlQ29uZmlnSAASNQoFYXVkaW8YCCABKAsyJC5tZXNodGFzdGljLk1vZHVsZUNvbmZpZy5BdWRpb0NvbmZpZ0gAEkgKD3JlbW90ZV9oYXJkd2FyZRgJIAEoCzItLm1lc2h0YXN0aWMuTW9kdWxlQ29uZmlnLlJlbW90ZUhhcmR3YXJlQ29uZmlnSAASRAoNbmVpZ2hib3JfaW5mbxgKIAEoCzIrLm1lc2h0YXN0aWMuTW9kdWxlQ29uZmlnLk5laWdoYm9ySW5mb0NvbmZpZ0gAEkoKEGFtYmllbnRfbGlnaHRpbmcYCyABKAsyLi5tZXNodGFzdGljLk1vZHVsZUNvbmZpZy5BbWJpZW50TGlnaHRpbmdDb25maWdIABJKChBkZXRlY3Rpb25fc2Vuc29yGAwgASgLMi4ubWVzaHRhc3RpYy5Nb2R1bGVDb25maWcuRGV0ZWN0aW9uU2Vuc29yQ29uZmlnSAASPwoKcGF4Y291bnRlchgNIAEoCzIpLm1lc2h0YXN0aWMuTW9kdWxlQ29uZmlnLlBheGNvdW50ZXJDb25maWdIABqwAgoKTVFUVENvbmZpZxIPCgdlbmFibGVkGAEgASgIEg8KB2FkZHJlc3MYAiABKAkSEAoIdXNlcm5hbWUYAyABKAkSEAoIcGFzc3dvcmQYBCABKAkSGgoSZW5jcnlwdGlvbl9lbmFibGVkGAUgASgIEhQKDGpzb25fZW5hYmxlZBgGIAEoCBITCgt0bHNfZW5hYmxlZBgHIAEoCBIMCgRyb290GAggASgJEh8KF3Byb3h5X3RvX2NsaWVudF9lbmFibGVkGAkgASgIEh0KFW1hcF9yZXBvcnRpbmdfZW5hYmxlZBgKIAEoCBJHChNtYXBfcmVwb3J0X3NldHRpbmdzGAsgASgLMioubWVzaHRhc3RpYy5Nb2R1bGVDb25maWcuTWFwUmVwb3J0U2V0dGluZ3MabgoRTWFwUmVwb3J0U2V0dGluZ3MSHQoVcHVibGlzaF9pbnRlcnZhbF9zZWNzGAEgASgNEhoKEnBvc2l0aW9uX3ByZWNpc2lvbhgCIAEoDRIeChZzaG91bGRfcmVwb3J0X2xvY2F0aW9uGAMgASgIGoIBChRSZW1vdGVIYXJkd2FyZUNvbmZpZxIPCgdlbmFibGVkGAEgASgIEiIKGmFsbG93X3VuZGVmaW5lZF9waW5fYWNjZXNzGAIgASgIEjUKDmF2YWlsYWJsZV9waW5zGAMgAygLMh0ubWVzaHRhc3RpYy5SZW1vdGVIYXJkd2FyZVBpbhpaChJOZWlnaGJvckluZm9Db25maWcSDwoHZW5hYmxlZBgBIAEoCBIXCg91cGRhdGVfaW50ZXJ2YWwYAiABKA0SGgoSdHJhbnNtaXRfb3Zlcl9sb3JhGAMgASgIGpcDChVEZXRlY3Rpb25TZW5zb3JDb25maWcSDwoHZW5hYmxlZBgBIAEoCBIeChZtaW5pbXVtX2Jyb2FkY2FzdF9zZWNzGAIgASgNEhwKFHN0YXRlX2Jyb2FkY2FzdF9zZWNzGAMgASgNEhEKCXNlbmRfYmVsbBgEIAEoCBIMCgRuYW1lGAUgASgJEhMKC21vbml0b3JfcGluGAYgASgNEloKFmRldGVjdGlvbl90cmlnZ2VyX3R5cGUYByABKA4yOi5tZXNodGFzdGljLk1vZHVsZUNvbmZpZy5EZXRlY3Rpb25TZW5zb3JDb25maWcuVHJpZ2dlclR5cGUSEgoKdXNlX3B1bGx1cBgIIAEoCCKIAQoLVHJpZ2dlclR5cGUSDQoJTE9HSUNfTE9XEAASDgoKTE9HSUNfSElHSBABEhAKDEZBTExJTkdfRURHRRACEg8KC1JJU0lOR19FREdFEAMSGgoWRUlUSEVSX0VER0VfQUNUSVZFX0xPVxAEEhsKF0VJVEhFUl9FREdFX0FDVElWRV9ISUdIEAUa5AIKC0F1ZGlvQ29uZmlnEhYKDmNvZGVjMl9lbmFibGVkGAEgASgIEg8KB3B0dF9waW4YAiABKA0SQAoHYml0cmF0ZRgDIAEoDjIvLm1lc2h0YXN0aWMuTW9kdWxlQ29uZmlnLkF1ZGlvQ29uZmlnLkF1ZGlvX0JhdWQSDgoGaTJzX3dzGAQgASgNEg4KBmkyc19zZBgFIAEoDRIPCgdpMnNfZGluGAYgASgNEg8KB2kyc19zY2sYByABKA0ipwEKCkF1ZGlvX0JhdWQSEgoOQ09ERUMyX0RFRkFVTFQQABIPCgtDT0RFQzJfMzIwMBABEg8KC0NPREVDMl8yNDAwEAISDwoLQ09ERUMyXzE2MDAQAxIPCgtDT0RFQzJfMTQwMBAEEg8KC0NPREVDMl8xMzAwEAUSDwoLQ09ERUMyXzEyMDAQBhIOCgpDT0RFQzJfNzAwEAcSDwoLQ09ERUMyXzcwMEIQCBp2ChBQYXhjb3VudGVyQ29uZmlnEg8KB2VuYWJsZWQYASABKAgSIgoacGF4Y291bnRlcl91cGRhdGVfaW50ZXJ2YWwYAiABKA0SFgoOd2lmaV90aHJlc2hvbGQYAyABKAUSFQoNYmxlX3RocmVzaG9sZBgEIAEoBRr9BAoMU2VyaWFsQ29uZmlnEg8KB2VuYWJsZWQYASABKAgSDAoEZWNobxgCIAEoCBILCgNyeGQYAyABKA0SCwoDdHhkGAQgASgNEj8KBGJhdWQYBSABKA4yMS5tZXNodGFzdGljLk1vZHVsZUNvbmZpZy5TZXJpYWxDb25maWcuU2VyaWFsX0JhdWQSDwoHdGltZW91dBgGIAEoDRI/CgRtb2RlGAcgASgOMjEubWVzaHRhc3RpYy5Nb2R1bGVDb25maWcuU2VyaWFsQ29uZmlnLlNlcmlhbF9Nb2RlEiQKHG92ZXJyaWRlX2NvbnNvbGVfc2VyaWFsX3BvcnQYCCABKAgiigIKC1NlcmlhbF9CYXVkEhAKDEJBVURfREVGQVVMVBAAEgwKCEJBVURfMTEwEAESDAoIQkFVRF8zMDAQAhIMCghCQVVEXzYwMBADEg0KCUJBVURfMTIwMBAEEg0KCUJBVURfMjQwMBAFEg0KCUJBVURfNDgwMBAGEg0KCUJBVURfOTYwMBAHEg4KCkJBVURfMTkyMDAQCBIOCgpCQVVEXzM4NDAwEAkSDgoKQkFVRF81NzYwMBAKEg8KC0JBVURfMTE1MjAwEAsSDwoLQkFVRF8yMzA0MDAQDBIPCgtCQVVEXzQ2MDgwMBANEg8KC0JBVURfNTc2MDAwEA4SDwoLQkFVRF85MjE2MDAQDyJuCgtTZXJpYWxfTW9kZRILCgdERUZBVUxUEAASCgoGU0lNUExFEAESCQoFUFJPVE8QAhILCgdURVhUTVNHEAMSCAoETk1FQRAEEgsKB0NBTFRPUE8QBRIICgRXUzg1EAYSDQoJVkVfRElSRUNUEAca6QIKGkV4dGVybmFsTm90aWZpY2F0aW9uQ29uZmlnEg8KB2VuYWJsZWQYASABKAgSEQoJb3V0cHV0X21zGAIgASgNEg4KBm91dHB1dBgDIAEoDRIUCgxvdXRwdXRfdmlicmEYCCABKA0SFQoNb3V0cHV0X2J1enplchgJIAEoDRIOCgZhY3RpdmUYBCABKAgSFQoNYWxlcnRfbWVzc2FnZRgFIAEoCBIbChNhbGVydF9tZXNzYWdlX3ZpYnJhGAogASgIEhwKFGFsZXJ0X21lc3NhZ2VfYnV6emVyGAsgASgIEhIKCmFsZXJ0X2JlbGwYBiABKAgSGAoQYWxlcnRfYmVsbF92aWJyYRgMIAEoCBIZChFhbGVydF9iZWxsX2J1enplchgNIAEoCBIPCgd1c2VfcHdtGAcgASgIEhMKC25hZ190aW1lb3V0GA4gASgNEhkKEXVzZV9pMnNfYXNfYnV6emVyGA8gASgIGpcBChJTdG9yZUZvcndhcmRDb25maWcSDwoHZW5hYmxlZBgBIAEoCBIRCgloZWFydGJlYXQYAiABKAgSDwoHcmVjb3JkcxgDIAEoDRIaChJoaXN0b3J5X3JldHVybl9tYXgYBCABKA0SHQoVaGlzdG9yeV9yZXR1cm5fd2luZG93GAUgASgNEhEKCWlzX3NlcnZlchgGIAEoCBpACg9SYW5nZVRlc3RDb25maWcSDwoHZW5hYmxlZBgBIAEoCBIOCgZzZW5kZXIYAiABKA0SDAoEc2F2ZRgDIAEoCBrJAwoPVGVsZW1ldHJ5Q29uZmlnEh4KFmRldmljZV91cGRhdGVfaW50ZXJ2YWwYASABKA0SIwobZW52aXJvbm1lbnRfdXBkYXRlX2ludGVydmFsGAIgASgNEicKH2Vudmlyb25tZW50X21lYXN1cmVtZW50X2VuYWJsZWQYAyABKAgSIgoaZW52aXJvbm1lbnRfc2NyZWVuX2VuYWJsZWQYBCABKAgSJgoeZW52aXJvbm1lbnRfZGlzcGxheV9mYWhyZW5oZWl0GAUgASgIEhsKE2Fpcl9xdWFsaXR5X2VuYWJsZWQYBiABKAgSHAoUYWlyX3F1YWxpdHlfaW50ZXJ2YWwYByABKA0SIQoZcG93ZXJfbWVhc3VyZW1lbnRfZW5hYmxlZBgIIAEoCBIdChVwb3dlcl91cGRhdGVfaW50ZXJ2YWwYCSABKA0SHAoUcG93ZXJfc2NyZWVuX2VuYWJsZWQYCiABKAgSIgoaaGVhbHRoX21lYXN1cmVtZW50X2VuYWJsZWQYCyABKAgSHgoWaGVhbHRoX3VwZGF0ZV9pbnRlcnZhbBgMIAEoDRIdChVoZWFsdGhfc2NyZWVuX2VuYWJsZWQYDSABKAga1gQKE0Nhbm5lZE1lc3NhZ2VDb25maWcSFwoPcm90YXJ5MV9lbmFibGVkGAEgASgIEhkKEWlucHV0YnJva2VyX3Bpbl9hGAIgASgNEhkKEWlucHV0YnJva2VyX3Bpbl9iGAMgASgNEh0KFWlucHV0YnJva2VyX3Bpbl9wcmVzcxgEIAEoDRJZChRpbnB1dGJyb2tlcl9ldmVudF9jdxgFIAEoDjI7Lm1lc2h0YXN0aWMuTW9kdWxlQ29uZmlnLkNhbm5lZE1lc3NhZ2VDb25maWcuSW5wdXRFdmVudENoYXISWgoVaW5wdXRicm9rZXJfZXZlbnRfY2N3GAYgASgOMjsubWVzaHRhc3RpYy5Nb2R1bGVDb25maWcuQ2FubmVkTWVzc2FnZUNvbmZpZy5JbnB1dEV2ZW50Q2hhchJcChdpbnB1dGJyb2tlcl9ldmVudF9wcmVzcxgHIAEoDjI7Lm1lc2h0YXN0aWMuTW9kdWxlQ29uZmlnLkNhbm5lZE1lc3NhZ2VDb25maWcuSW5wdXRFdmVudENoYXISFwoPdXBkb3duMV9lbmFibGVkGAggASgIEg8KB2VuYWJsZWQYCSABKAgSGgoSYWxsb3dfaW5wdXRfc291cmNlGAogASgJEhEKCXNlbmRfYmVsbBgLIAEoCCJjCg5JbnB1dEV2ZW50Q2hhchIICgROT05FEAASBgoCVVAQERIICgRET1dOEBISCAoETEVGVBATEgkKBVJJR0hUEBQSCgoGU0VMRUNUEAoSCAoEQkFDSxAbEgoKBkNBTkNFTBAYGmUKFUFtYmllbnRMaWdodGluZ0NvbmZpZxIRCglsZWRfc3RhdGUYASABKAgSDwoHY3VycmVudBgCIAEoDRILCgNyZWQYAyABKA0SDQoFZ3JlZW4YBCABKA0SDAoEYmx1ZRgFIAEoDUIRCg9wYXlsb2FkX3ZhcmlhbnQiZAoRUmVtb3RlSGFyZHdhcmVQaW4SEAoIZ3Bpb19waW4YASABKA0SDAoEbmFtZRgCIAEoCRIvCgR0eXBlGAMgASgOMiEubWVzaHRhc3RpYy5SZW1vdGVIYXJkd2FyZVBpblR5cGUqSQoVUmVtb3RlSGFyZHdhcmVQaW5UeXBlEgsKB1VOS05PV04QABIQCgxESUdJVEFMX1JFQUQQARIRCg1ESUdJVEFMX1dSSVRFEAJCZwoTY29tLmdlZWtzdmlsbGUubWVzaEISTW9kdWxlQ29uZmlnUHJvdG9zWiJnaXRodWIuY29tL21lc2h0YXN0aWMvZ28vZ2VuZXJhdGVkqgIUTWVzaHRhc3RpYy5Qcm90b2J1ZnO6AgBiBnByb3RvMw"), Rs = /* @__PURE__ */ E(k, 0), zs = /* @__PURE__ */ E(k, 0, 0), Bs = /* @__PURE__ */ E(k, 0, 1), Vs = /* @__PURE__ */ E(k, 0, 2), Hs = /* @__PURE__ */ E(k, 0, 3), Us = /* @__PURE__ */ E(k, 0, 4), Ws = /* @__PURE__ */ function(e) {
	return e[e.LOGIC_LOW = 0] = "LOGIC_LOW", e[e.LOGIC_HIGH = 1] = "LOGIC_HIGH", e[e.FALLING_EDGE = 2] = "FALLING_EDGE", e[e.RISING_EDGE = 3] = "RISING_EDGE", e[e.EITHER_EDGE_ACTIVE_LOW = 4] = "EITHER_EDGE_ACTIVE_LOW", e[e.EITHER_EDGE_ACTIVE_HIGH = 5] = "EITHER_EDGE_ACTIVE_HIGH", e;
}({}), Gs = /* @__PURE__ */ C(k, 0, 4, 0), Ks = /* @__PURE__ */ E(k, 0, 5), qs = /* @__PURE__ */ function(e) {
	return e[e.CODEC2_DEFAULT = 0] = "CODEC2_DEFAULT", e[e.CODEC2_3200 = 1] = "CODEC2_3200", e[e.CODEC2_2400 = 2] = "CODEC2_2400", e[e.CODEC2_1600 = 3] = "CODEC2_1600", e[e.CODEC2_1400 = 4] = "CODEC2_1400", e[e.CODEC2_1300 = 5] = "CODEC2_1300", e[e.CODEC2_1200 = 6] = "CODEC2_1200", e[e.CODEC2_700 = 7] = "CODEC2_700", e[e.CODEC2_700B = 8] = "CODEC2_700B", e;
}({}), Js = /* @__PURE__ */ C(k, 0, 5, 0), Ys = /* @__PURE__ */ E(k, 0, 6), Xs = /* @__PURE__ */ E(k, 0, 7), Zs = /* @__PURE__ */ function(e) {
	return e[e.BAUD_DEFAULT = 0] = "BAUD_DEFAULT", e[e.BAUD_110 = 1] = "BAUD_110", e[e.BAUD_300 = 2] = "BAUD_300", e[e.BAUD_600 = 3] = "BAUD_600", e[e.BAUD_1200 = 4] = "BAUD_1200", e[e.BAUD_2400 = 5] = "BAUD_2400", e[e.BAUD_4800 = 6] = "BAUD_4800", e[e.BAUD_9600 = 7] = "BAUD_9600", e[e.BAUD_19200 = 8] = "BAUD_19200", e[e.BAUD_38400 = 9] = "BAUD_38400", e[e.BAUD_57600 = 10] = "BAUD_57600", e[e.BAUD_115200 = 11] = "BAUD_115200", e[e.BAUD_230400 = 12] = "BAUD_230400", e[e.BAUD_460800 = 13] = "BAUD_460800", e[e.BAUD_576000 = 14] = "BAUD_576000", e[e.BAUD_921600 = 15] = "BAUD_921600", e;
}({}), Qs = /* @__PURE__ */ C(k, 0, 7, 0), $s = /* @__PURE__ */ function(e) {
	return e[e.DEFAULT = 0] = "DEFAULT", e[e.SIMPLE = 1] = "SIMPLE", e[e.PROTO = 2] = "PROTO", e[e.TEXTMSG = 3] = "TEXTMSG", e[e.NMEA = 4] = "NMEA", e[e.CALTOPO = 5] = "CALTOPO", e[e.WS85 = 6] = "WS85", e[e.VE_DIRECT = 7] = "VE_DIRECT", e;
}({}), ec = /* @__PURE__ */ C(k, 0, 7, 1), tc = /* @__PURE__ */ E(k, 0, 8), nc = /* @__PURE__ */ E(k, 0, 9), rc = /* @__PURE__ */ E(k, 0, 10), ic = /* @__PURE__ */ E(k, 0, 11), ac = /* @__PURE__ */ E(k, 0, 12), oc = /* @__PURE__ */ function(e) {
	return e[e.NONE = 0] = "NONE", e[e.UP = 17] = "UP", e[e.DOWN = 18] = "DOWN", e[e.LEFT = 19] = "LEFT", e[e.RIGHT = 20] = "RIGHT", e[e.SELECT = 10] = "SELECT", e[e.BACK = 27] = "BACK", e[e.CANCEL = 24] = "CANCEL", e;
}({}), sc = /* @__PURE__ */ C(k, 0, 12, 0), cc = /* @__PURE__ */ E(k, 0, 13), lc = /* @__PURE__ */ E(k, 1), uc = /* @__PURE__ */ function(e) {
	return e[e.UNKNOWN = 0] = "UNKNOWN", e[e.DIGITAL_READ = 1] = "DIGITAL_READ", e[e.DIGITAL_WRITE = 2] = "DIGITAL_WRITE", e;
}({}), dc = /* @__PURE__ */ C(k, 0), A = g({
	PortNum: () => pc,
	PortNumSchema: () => mc,
	file_portnums: () => fc
}), fc = /* @__PURE__ */ T("Cg5wb3J0bnVtcy5wcm90bxIKbWVzaHRhc3RpYyrlBAoHUG9ydE51bRIPCgtVTktOT1dOX0FQUBAAEhQKEFRFWFRfTUVTU0FHRV9BUFAQARIXChNSRU1PVEVfSEFSRFdBUkVfQVBQEAISEAoMUE9TSVRJT05fQVBQEAMSEAoMTk9ERUlORk9fQVBQEAQSDwoLUk9VVElOR19BUFAQBRINCglBRE1JTl9BUFAQBhIfChtURVhUX01FU1NBR0VfQ09NUFJFU1NFRF9BUFAQBxIQCgxXQVlQT0lOVF9BUFAQCBINCglBVURJT19BUFAQCRIYChRERVRFQ1RJT05fU0VOU09SX0FQUBAKEg0KCUFMRVJUX0FQUBALEhgKFEtFWV9WRVJJRklDQVRJT05fQVBQEAwSDQoJUkVQTFlfQVBQECASEQoNSVBfVFVOTkVMX0FQUBAhEhIKDlBBWENPVU5URVJfQVBQECISDgoKU0VSSUFMX0FQUBBAEhUKEVNUT1JFX0ZPUldBUkRfQVBQEEESEgoOUkFOR0VfVEVTVF9BUFAQQhIRCg1URUxFTUVUUllfQVBQEEMSCwoHWlBTX0FQUBBEEhEKDVNJTVVMQVRPUl9BUFAQRRISCg5UUkFDRVJPVVRFX0FQUBBGEhQKEE5FSUdIQk9SSU5GT19BUFAQRxIPCgtBVEFLX1BMVUdJThBIEhIKDk1BUF9SRVBPUlRfQVBQEEkSEwoPUE9XRVJTVFJFU1NfQVBQEEoSGAoUUkVUSUNVTFVNX1RVTk5FTF9BUFAQTBIQCgtQUklWQVRFX0FQUBCAAhITCg5BVEFLX0ZPUldBUkRFUhCBAhIICgNNQVgQ/wNCXQoTY29tLmdlZWtzdmlsbGUubWVzaEIIUG9ydG51bXNaImdpdGh1Yi5jb20vbWVzaHRhc3RpYy9nby9nZW5lcmF0ZWSqAhRNZXNodGFzdGljLlByb3RvYnVmc7oCAGIGcHJvdG8z"), pc = /* @__PURE__ */ function(e) {
	return e[e.UNKNOWN_APP = 0] = "UNKNOWN_APP", e[e.TEXT_MESSAGE_APP = 1] = "TEXT_MESSAGE_APP", e[e.REMOTE_HARDWARE_APP = 2] = "REMOTE_HARDWARE_APP", e[e.POSITION_APP = 3] = "POSITION_APP", e[e.NODEINFO_APP = 4] = "NODEINFO_APP", e[e.ROUTING_APP = 5] = "ROUTING_APP", e[e.ADMIN_APP = 6] = "ADMIN_APP", e[e.TEXT_MESSAGE_COMPRESSED_APP = 7] = "TEXT_MESSAGE_COMPRESSED_APP", e[e.WAYPOINT_APP = 8] = "WAYPOINT_APP", e[e.AUDIO_APP = 9] = "AUDIO_APP", e[e.DETECTION_SENSOR_APP = 10] = "DETECTION_SENSOR_APP", e[e.ALERT_APP = 11] = "ALERT_APP", e[e.KEY_VERIFICATION_APP = 12] = "KEY_VERIFICATION_APP", e[e.REPLY_APP = 32] = "REPLY_APP", e[e.IP_TUNNEL_APP = 33] = "IP_TUNNEL_APP", e[e.PAXCOUNTER_APP = 34] = "PAXCOUNTER_APP", e[e.SERIAL_APP = 64] = "SERIAL_APP", e[e.STORE_FORWARD_APP = 65] = "STORE_FORWARD_APP", e[e.RANGE_TEST_APP = 66] = "RANGE_TEST_APP", e[e.TELEMETRY_APP = 67] = "TELEMETRY_APP", e[e.ZPS_APP = 68] = "ZPS_APP", e[e.SIMULATOR_APP = 69] = "SIMULATOR_APP", e[e.TRACEROUTE_APP = 70] = "TRACEROUTE_APP", e[e.NEIGHBORINFO_APP = 71] = "NEIGHBORINFO_APP", e[e.ATAK_PLUGIN = 72] = "ATAK_PLUGIN", e[e.MAP_REPORT_APP = 73] = "MAP_REPORT_APP", e[e.POWERSTRESS_APP = 74] = "POWERSTRESS_APP", e[e.RETICULUM_TUNNEL_APP = 76] = "RETICULUM_TUNNEL_APP", e[e.PRIVATE_APP = 256] = "PRIVATE_APP", e[e.ATAK_FORWARDER = 257] = "ATAK_FORWARDER", e[e.MAX = 511] = "MAX", e;
}({}), mc = /* @__PURE__ */ C(fc, 0), hc = g({
	AirQualityMetricsSchema: () => yc,
	DeviceMetricsSchema: () => gc,
	EnvironmentMetricsSchema: () => _c,
	HealthMetricsSchema: () => xc,
	HostMetricsSchema: () => Sc,
	LocalStatsSchema: () => bc,
	Nau7802ConfigSchema: () => wc,
	PowerMetricsSchema: () => vc,
	TelemetrySchema: () => Cc,
	TelemetrySensorType: () => Tc,
	TelemetrySensorTypeSchema: () => Ec,
	file_telemetry: () => j
}), j = /* @__PURE__ */ T("Cg90ZWxlbWV0cnkucHJvdG8SCm1lc2h0YXN0aWMi8wEKDURldmljZU1ldHJpY3MSGgoNYmF0dGVyeV9sZXZlbBgBIAEoDUgAiAEBEhQKB3ZvbHRhZ2UYAiABKAJIAYgBARIgChNjaGFubmVsX3V0aWxpemF0aW9uGAMgASgCSAKIAQESGAoLYWlyX3V0aWxfdHgYBCABKAJIA4gBARIbCg51cHRpbWVfc2Vjb25kcxgFIAEoDUgEiAEBQhAKDl9iYXR0ZXJ5X2xldmVsQgoKCF92b2x0YWdlQhYKFF9jaGFubmVsX3V0aWxpemF0aW9uQg4KDF9haXJfdXRpbF90eEIRCg9fdXB0aW1lX3NlY29uZHMiggcKEkVudmlyb25tZW50TWV0cmljcxIYCgt0ZW1wZXJhdHVyZRgBIAEoAkgAiAEBEh4KEXJlbGF0aXZlX2h1bWlkaXR5GAIgASgCSAGIAQESIAoTYmFyb21ldHJpY19wcmVzc3VyZRgDIAEoAkgCiAEBEhsKDmdhc19yZXNpc3RhbmNlGAQgASgCSAOIAQESFAoHdm9sdGFnZRgFIAEoAkgEiAEBEhQKB2N1cnJlbnQYBiABKAJIBYgBARIQCgNpYXEYByABKA1IBogBARIVCghkaXN0YW5jZRgIIAEoAkgHiAEBEhAKA2x1eBgJIAEoAkgIiAEBEhYKCXdoaXRlX2x1eBgKIAEoAkgJiAEBEhMKBmlyX2x1eBgLIAEoAkgKiAEBEhMKBnV2X2x1eBgMIAEoAkgLiAEBEhsKDndpbmRfZGlyZWN0aW9uGA0gASgNSAyIAQESFwoKd2luZF9zcGVlZBgOIAEoAkgNiAEBEhMKBndlaWdodBgPIAEoAkgOiAEBEhYKCXdpbmRfZ3VzdBgQIAEoAkgPiAEBEhYKCXdpbmRfbHVsbBgRIAEoAkgQiAEBEhYKCXJhZGlhdGlvbhgSIAEoAkgRiAEBEhgKC3JhaW5mYWxsXzFoGBMgASgCSBKIAQESGQoMcmFpbmZhbGxfMjRoGBQgASgCSBOIAQESGgoNc29pbF9tb2lzdHVyZRgVIAEoDUgUiAEBEh0KEHNvaWxfdGVtcGVyYXR1cmUYFiABKAJIFYgBAUIOCgxfdGVtcGVyYXR1cmVCFAoSX3JlbGF0aXZlX2h1bWlkaXR5QhYKFF9iYXJvbWV0cmljX3ByZXNzdXJlQhEKD19nYXNfcmVzaXN0YW5jZUIKCghfdm9sdGFnZUIKCghfY3VycmVudEIGCgRfaWFxQgsKCV9kaXN0YW5jZUIGCgRfbHV4QgwKCl93aGl0ZV9sdXhCCQoHX2lyX2x1eEIJCgdfdXZfbHV4QhEKD193aW5kX2RpcmVjdGlvbkINCgtfd2luZF9zcGVlZEIJCgdfd2VpZ2h0QgwKCl93aW5kX2d1c3RCDAoKX3dpbmRfbHVsbEIMCgpfcmFkaWF0aW9uQg4KDF9yYWluZmFsbF8xaEIPCg1fcmFpbmZhbGxfMjRoQhAKDl9zb2lsX21vaXN0dXJlQhMKEV9zb2lsX3RlbXBlcmF0dXJlIooCCgxQb3dlck1ldHJpY3MSGAoLY2gxX3ZvbHRhZ2UYASABKAJIAIgBARIYCgtjaDFfY3VycmVudBgCIAEoAkgBiAEBEhgKC2NoMl92b2x0YWdlGAMgASgCSAKIAQESGAoLY2gyX2N1cnJlbnQYBCABKAJIA4gBARIYCgtjaDNfdm9sdGFnZRgFIAEoAkgEiAEBEhgKC2NoM19jdXJyZW50GAYgASgCSAWIAQFCDgoMX2NoMV92b2x0YWdlQg4KDF9jaDFfY3VycmVudEIOCgxfY2gyX3ZvbHRhZ2VCDgoMX2NoMl9jdXJyZW50Qg4KDF9jaDNfdm9sdGFnZUIOCgxfY2gzX2N1cnJlbnQihQUKEUFpclF1YWxpdHlNZXRyaWNzEhoKDXBtMTBfc3RhbmRhcmQYASABKA1IAIgBARIaCg1wbTI1X3N0YW5kYXJkGAIgASgNSAGIAQESGwoOcG0xMDBfc3RhbmRhcmQYAyABKA1IAogBARIfChJwbTEwX2Vudmlyb25tZW50YWwYBCABKA1IA4gBARIfChJwbTI1X2Vudmlyb25tZW50YWwYBSABKA1IBIgBARIgChNwbTEwMF9lbnZpcm9ubWVudGFsGAYgASgNSAWIAQESGwoOcGFydGljbGVzXzAzdW0YByABKA1IBogBARIbCg5wYXJ0aWNsZXNfMDV1bRgIIAEoDUgHiAEBEhsKDnBhcnRpY2xlc18xMHVtGAkgASgNSAiIAQESGwoOcGFydGljbGVzXzI1dW0YCiABKA1ICYgBARIbCg5wYXJ0aWNsZXNfNTB1bRgLIAEoDUgKiAEBEhwKD3BhcnRpY2xlc18xMDB1bRgMIAEoDUgLiAEBEhAKA2NvMhgNIAEoDUgMiAEBQhAKDl9wbTEwX3N0YW5kYXJkQhAKDl9wbTI1X3N0YW5kYXJkQhEKD19wbTEwMF9zdGFuZGFyZEIVChNfcG0xMF9lbnZpcm9ubWVudGFsQhUKE19wbTI1X2Vudmlyb25tZW50YWxCFgoUX3BtMTAwX2Vudmlyb25tZW50YWxCEQoPX3BhcnRpY2xlc18wM3VtQhEKD19wYXJ0aWNsZXNfMDV1bUIRCg9fcGFydGljbGVzXzEwdW1CEQoPX3BhcnRpY2xlc18yNXVtQhEKD19wYXJ0aWNsZXNfNTB1bUISChBfcGFydGljbGVzXzEwMHVtQgYKBF9jbzIi0gIKCkxvY2FsU3RhdHMSFgoOdXB0aW1lX3NlY29uZHMYASABKA0SGwoTY2hhbm5lbF91dGlsaXphdGlvbhgCIAEoAhITCgthaXJfdXRpbF90eBgDIAEoAhIWCg5udW1fcGFja2V0c190eBgEIAEoDRIWCg5udW1fcGFja2V0c19yeBgFIAEoDRIaChJudW1fcGFja2V0c19yeF9iYWQYBiABKA0SGAoQbnVtX29ubGluZV9ub2RlcxgHIAEoDRIXCg9udW1fdG90YWxfbm9kZXMYCCABKA0SEwoLbnVtX3J4X2R1cGUYCSABKA0SFAoMbnVtX3R4X3JlbGF5GAogASgNEh0KFW51bV90eF9yZWxheV9jYW5jZWxlZBgLIAEoDRIYChBoZWFwX3RvdGFsX2J5dGVzGAwgASgNEhcKD2hlYXBfZnJlZV9ieXRlcxgNIAEoDSJ7Cg1IZWFsdGhNZXRyaWNzEhYKCWhlYXJ0X2JwbRgBIAEoDUgAiAEBEhEKBHNwTzIYAiABKA1IAYgBARIYCgt0ZW1wZXJhdHVyZRgDIAEoAkgCiAEBQgwKCl9oZWFydF9icG1CBwoFX3NwTzJCDgoMX3RlbXBlcmF0dXJlIpECCgtIb3N0TWV0cmljcxIWCg51cHRpbWVfc2Vjb25kcxgBIAEoDRIVCg1mcmVlbWVtX2J5dGVzGAIgASgEEhcKD2Rpc2tmcmVlMV9ieXRlcxgDIAEoBBIcCg9kaXNrZnJlZTJfYnl0ZXMYBCABKARIAIgBARIcCg9kaXNrZnJlZTNfYnl0ZXMYBSABKARIAYgBARINCgVsb2FkMRgGIAEoDRINCgVsb2FkNRgHIAEoDRIOCgZsb2FkMTUYCCABKA0SGAoLdXNlcl9zdHJpbmcYCSABKAlIAogBAUISChBfZGlza2ZyZWUyX2J5dGVzQhIKEF9kaXNrZnJlZTNfYnl0ZXNCDgoMX3VzZXJfc3RyaW5nIp4DCglUZWxlbWV0cnkSDAoEdGltZRgBIAEoBxIzCg5kZXZpY2VfbWV0cmljcxgCIAEoCzIZLm1lc2h0YXN0aWMuRGV2aWNlTWV0cmljc0gAEj0KE2Vudmlyb25tZW50X21ldHJpY3MYAyABKAsyHi5tZXNodGFzdGljLkVudmlyb25tZW50TWV0cmljc0gAEjwKE2Fpcl9xdWFsaXR5X21ldHJpY3MYBCABKAsyHS5tZXNodGFzdGljLkFpclF1YWxpdHlNZXRyaWNzSAASMQoNcG93ZXJfbWV0cmljcxgFIAEoCzIYLm1lc2h0YXN0aWMuUG93ZXJNZXRyaWNzSAASLQoLbG9jYWxfc3RhdHMYBiABKAsyFi5tZXNodGFzdGljLkxvY2FsU3RhdHNIABIzCg5oZWFsdGhfbWV0cmljcxgHIAEoCzIZLm1lc2h0YXN0aWMuSGVhbHRoTWV0cmljc0gAEi8KDGhvc3RfbWV0cmljcxgIIAEoCzIXLm1lc2h0YXN0aWMuSG9zdE1ldHJpY3NIAEIJCgd2YXJpYW50Ij4KDU5hdTc4MDJDb25maWcSEgoKemVyb09mZnNldBgBIAEoBRIZChFjYWxpYnJhdGlvbkZhY3RvchgCIAEoAiqsBAoTVGVsZW1ldHJ5U2Vuc29yVHlwZRIQCgxTRU5TT1JfVU5TRVQQABIKCgZCTUUyODAQARIKCgZCTUU2ODAQAhILCgdNQ1A5ODA4EAMSCgoGSU5BMjYwEAQSCgoGSU5BMjE5EAUSCgoGQk1QMjgwEAYSCQoFU0hUQzMQBxIJCgVMUFMyMhAIEgsKB1FNQzYzMTAQCRILCgdRTUk4NjU4EAoSDAoIUU1DNTg4M0wQCxIJCgVTSFQzMRAMEgwKCFBNU0EwMDNJEA0SCwoHSU5BMzIyMRAOEgoKBkJNUDA4NRAPEgwKCFJDV0w5NjIwEBASCQoFU0hUNFgQERIMCghWRU1MNzcwMBASEgwKCE1MWDkwNjMyEBMSCwoHT1BUMzAwMRAUEgwKCExUUjM5MFVWEBUSDgoKVFNMMjU5MTFGThAWEgkKBUFIVDEwEBcSEAoMREZST0JPVF9MQVJLEBgSCwoHTkFVNzgwMhAZEgoKBkJNUDNYWBAaEgwKCElDTTIwOTQ4EBsSDAoITUFYMTcwNDgQHBIRCg1DVVNUT01fU0VOU09SEB0SDAoITUFYMzAxMDIQHhIMCghNTFg5MDYxNBAfEgkKBVNDRDRYECASCwoHUkFEU0VOUxAhEgoKBklOQTIyNhAiEhAKDERGUk9CT1RfUkFJThAjEgoKBkRQUzMxMBAkEgwKCFJBSzEyMDM1ECUSDAoITUFYMTcyNjEQJhILCgdQQ1QyMDc1ECdCZAoTY29tLmdlZWtzdmlsbGUubWVzaEIPVGVsZW1ldHJ5UHJvdG9zWiJnaXRodWIuY29tL21lc2h0YXN0aWMvZ28vZ2VuZXJhdGVkqgIUTWVzaHRhc3RpYy5Qcm90b2J1ZnO6AgBiBnByb3RvMw"), gc = /* @__PURE__ */ E(j, 0), _c = /* @__PURE__ */ E(j, 1), vc = /* @__PURE__ */ E(j, 2), yc = /* @__PURE__ */ E(j, 3), bc = /* @__PURE__ */ E(j, 4), xc = /* @__PURE__ */ E(j, 5), Sc = /* @__PURE__ */ E(j, 6), Cc = /* @__PURE__ */ E(j, 7), wc = /* @__PURE__ */ E(j, 8), Tc = /* @__PURE__ */ function(e) {
	return e[e.SENSOR_UNSET = 0] = "SENSOR_UNSET", e[e.BME280 = 1] = "BME280", e[e.BME680 = 2] = "BME680", e[e.MCP9808 = 3] = "MCP9808", e[e.INA260 = 4] = "INA260", e[e.INA219 = 5] = "INA219", e[e.BMP280 = 6] = "BMP280", e[e.SHTC3 = 7] = "SHTC3", e[e.LPS22 = 8] = "LPS22", e[e.QMC6310 = 9] = "QMC6310", e[e.QMI8658 = 10] = "QMI8658", e[e.QMC5883L = 11] = "QMC5883L", e[e.SHT31 = 12] = "SHT31", e[e.PMSA003I = 13] = "PMSA003I", e[e.INA3221 = 14] = "INA3221", e[e.BMP085 = 15] = "BMP085", e[e.RCWL9620 = 16] = "RCWL9620", e[e.SHT4X = 17] = "SHT4X", e[e.VEML7700 = 18] = "VEML7700", e[e.MLX90632 = 19] = "MLX90632", e[e.OPT3001 = 20] = "OPT3001", e[e.LTR390UV = 21] = "LTR390UV", e[e.TSL25911FN = 22] = "TSL25911FN", e[e.AHT10 = 23] = "AHT10", e[e.DFROBOT_LARK = 24] = "DFROBOT_LARK", e[e.NAU7802 = 25] = "NAU7802", e[e.BMP3XX = 26] = "BMP3XX", e[e.ICM20948 = 27] = "ICM20948", e[e.MAX17048 = 28] = "MAX17048", e[e.CUSTOM_SENSOR = 29] = "CUSTOM_SENSOR", e[e.MAX30102 = 30] = "MAX30102", e[e.MLX90614 = 31] = "MLX90614", e[e.SCD4X = 32] = "SCD4X", e[e.RADSENS = 33] = "RADSENS", e[e.INA226 = 34] = "INA226", e[e.DFROBOT_RAIN = 35] = "DFROBOT_RAIN", e[e.DPS310 = 36] = "DPS310", e[e.RAK12035 = 37] = "RAK12035", e[e.MAX17261 = 38] = "MAX17261", e[e.PCT2075 = 39] = "PCT2075", e;
}({}), Ec = /* @__PURE__ */ C(j, 0), M = g({
	XModemSchema: () => Oc,
	XModem_Control: () => kc,
	XModem_ControlSchema: () => Ac,
	file_xmodem: () => Dc
}), Dc = /* @__PURE__ */ T("Cgx4bW9kZW0ucHJvdG8SCm1lc2h0YXN0aWMitgEKBlhNb2RlbRIrCgdjb250cm9sGAEgASgOMhoubWVzaHRhc3RpYy5YTW9kZW0uQ29udHJvbBILCgNzZXEYAiABKA0SDQoFY3JjMTYYAyABKA0SDgoGYnVmZmVyGAQgASgMIlMKB0NvbnRyb2wSBwoDTlVMEAASBwoDU09IEAESBwoDU1RYEAISBwoDRU9UEAQSBwoDQUNLEAYSBwoDTkFLEBUSBwoDQ0FOEBgSCQoFQ1RSTFoQGkJhChNjb20uZ2Vla3N2aWxsZS5tZXNoQgxYbW9kZW1Qcm90b3NaImdpdGh1Yi5jb20vbWVzaHRhc3RpYy9nby9nZW5lcmF0ZWSqAhRNZXNodGFzdGljLlByb3RvYnVmc7oCAGIGcHJvdG8z"), Oc = /* @__PURE__ */ E(Dc, 0), kc = /* @__PURE__ */ function(e) {
	return e[e.NUL = 0] = "NUL", e[e.SOH = 1] = "SOH", e[e.STX = 2] = "STX", e[e.EOT = 4] = "EOT", e[e.ACK = 6] = "ACK", e[e.NAK = 21] = "NAK", e[e.CAN = 24] = "CAN", e[e.CTRLZ = 26] = "CTRLZ", e;
}({}), Ac = /* @__PURE__ */ C(Dc, 0, 0), N = g({
	ChunkedPayloadResponseSchema: () => yl,
	ChunkedPayloadSchema: () => _l,
	ClientNotificationSchema: () => rl,
	CompressedSchema: () => dl,
	Constants: () => Sl,
	ConstantsSchema: () => Cl,
	CriticalErrorCode: () => wl,
	CriticalErrorCodeSchema: () => Tl,
	DataSchema: () => Vc,
	DeviceMetadataSchema: () => ml,
	DuplicatedPublicKeySchema: () => sl,
	ExcludedModules: () => El,
	ExcludedModulesSchema: () => Dl,
	FileInfoSchema: () => ll,
	FromRadioSchema: () => nl,
	HardwareModel: () => bl,
	HardwareModelSchema: () => xl,
	HeartbeatSchema: () => hl,
	KeyVerificationFinalSchema: () => ol,
	KeyVerificationNumberInformSchema: () => il,
	KeyVerificationNumberRequestSchema: () => al,
	KeyVerificationSchema: () => Hc,
	LogRecordSchema: () => Qc,
	LogRecord_Level: () => $c,
	LogRecord_LevelSchema: () => el,
	LowEntropyKeySchema: () => cl,
	MeshPacketSchema: () => Gc,
	MeshPacket_Delayed: () => Jc,
	MeshPacket_DelayedSchema: () => Yc,
	MeshPacket_Priority: () => Kc,
	MeshPacket_PrioritySchema: () => qc,
	MqttClientProxyMessageSchema: () => Wc,
	MyNodeInfoSchema: () => Zc,
	NeighborInfoSchema: () => fl,
	NeighborSchema: () => pl,
	NodeInfoSchema: () => Xc,
	NodeRemoteHardwarePinSchema: () => gl,
	PositionSchema: () => jc,
	Position_AltSource: () => Pc,
	Position_AltSourceSchema: () => Fc,
	Position_LocSource: () => Mc,
	Position_LocSourceSchema: () => Nc,
	QueueStatusSchema: () => tl,
	RouteDiscoverySchema: () => Lc,
	RoutingSchema: () => Rc,
	Routing_Error: () => zc,
	Routing_ErrorSchema: () => Bc,
	ToRadioSchema: () => ul,
	UserSchema: () => Ic,
	WaypointSchema: () => Uc,
	file_mesh: () => P,
	resend_chunksSchema: () => vl
}), P = /* @__PURE__ */ T("CgptZXNoLnByb3RvEgptZXNodGFzdGljIocHCghQb3NpdGlvbhIXCgpsYXRpdHVkZV9pGAEgASgPSACIAQESGAoLbG9uZ2l0dWRlX2kYAiABKA9IAYgBARIVCghhbHRpdHVkZRgDIAEoBUgCiAEBEgwKBHRpbWUYBCABKAcSNwoPbG9jYXRpb25fc291cmNlGAUgASgOMh4ubWVzaHRhc3RpYy5Qb3NpdGlvbi5Mb2NTb3VyY2USNwoPYWx0aXR1ZGVfc291cmNlGAYgASgOMh4ubWVzaHRhc3RpYy5Qb3NpdGlvbi5BbHRTb3VyY2USEQoJdGltZXN0YW1wGAcgASgHEh8KF3RpbWVzdGFtcF9taWxsaXNfYWRqdXN0GAggASgFEhkKDGFsdGl0dWRlX2hhZRgJIAEoEUgDiAEBEigKG2FsdGl0dWRlX2dlb2lkYWxfc2VwYXJhdGlvbhgKIAEoEUgEiAEBEgwKBFBET1AYCyABKA0SDAoESERPUBgMIAEoDRIMCgRWRE9QGA0gASgNEhQKDGdwc19hY2N1cmFjeRgOIAEoDRIZCgxncm91bmRfc3BlZWQYDyABKA1IBYgBARIZCgxncm91bmRfdHJhY2sYECABKA1IBogBARITCgtmaXhfcXVhbGl0eRgRIAEoDRIQCghmaXhfdHlwZRgSIAEoDRIUCgxzYXRzX2luX3ZpZXcYEyABKA0SEQoJc2Vuc29yX2lkGBQgASgNEhMKC25leHRfdXBkYXRlGBUgASgNEhIKCnNlcV9udW1iZXIYFiABKA0SFgoOcHJlY2lzaW9uX2JpdHMYFyABKA0iTgoJTG9jU291cmNlEg0KCUxPQ19VTlNFVBAAEg4KCkxPQ19NQU5VQUwQARIQCgxMT0NfSU5URVJOQUwQAhIQCgxMT0NfRVhURVJOQUwQAyJiCglBbHRTb3VyY2USDQoJQUxUX1VOU0VUEAASDgoKQUxUX01BTlVBTBABEhAKDEFMVF9JTlRFUk5BTBACEhAKDEFMVF9FWFRFUk5BTBADEhIKDkFMVF9CQVJPTUVUUklDEARCDQoLX2xhdGl0dWRlX2lCDgoMX2xvbmdpdHVkZV9pQgsKCV9hbHRpdHVkZUIPCg1fYWx0aXR1ZGVfaGFlQh4KHF9hbHRpdHVkZV9nZW9pZGFsX3NlcGFyYXRpb25CDwoNX2dyb3VuZF9zcGVlZEIPCg1fZ3JvdW5kX3RyYWNrIooCCgRVc2VyEgoKAmlkGAEgASgJEhEKCWxvbmdfbmFtZRgCIAEoCRISCgpzaG9ydF9uYW1lGAMgASgJEhMKB21hY2FkZHIYBCABKAxCAhgBEisKCGh3X21vZGVsGAUgASgOMhkubWVzaHRhc3RpYy5IYXJkd2FyZU1vZGVsEhMKC2lzX2xpY2Vuc2VkGAYgASgIEjIKBHJvbGUYByABKA4yJC5tZXNodGFzdGljLkNvbmZpZy5EZXZpY2VDb25maWcuUm9sZRISCgpwdWJsaWNfa2V5GAggASgMEhwKD2lzX3VubWVzc2FnYWJsZRgJIAEoCEgAiAEBQhIKEF9pc191bm1lc3NhZ2FibGUiWgoOUm91dGVEaXNjb3ZlcnkSDQoFcm91dGUYASADKAcSEwoLc25yX3Rvd2FyZHMYAiADKAUSEgoKcm91dGVfYmFjaxgDIAMoBxIQCghzbnJfYmFjaxgEIAMoBSLiAwoHUm91dGluZxIzCg1yb3V0ZV9yZXF1ZXN0GAEgASgLMhoubWVzaHRhc3RpYy5Sb3V0ZURpc2NvdmVyeUgAEjEKC3JvdXRlX3JlcGx5GAIgASgLMhoubWVzaHRhc3RpYy5Sb3V0ZURpc2NvdmVyeUgAEjEKDGVycm9yX3JlYXNvbhgDIAEoDjIZLm1lc2h0YXN0aWMuUm91dGluZy5FcnJvckgAIrACCgVFcnJvchIICgROT05FEAASDAoITk9fUk9VVEUQARILCgdHT1RfTkFLEAISCwoHVElNRU9VVBADEhAKDE5PX0lOVEVSRkFDRRAEEhIKDk1BWF9SRVRSQU5TTUlUEAUSDgoKTk9fQ0hBTk5FTBAGEg0KCVRPT19MQVJHRRAHEg8KC05PX1JFU1BPTlNFEAgSFAoQRFVUWV9DWUNMRV9MSU1JVBAJEg8KC0JBRF9SRVFVRVNUECASEgoOTk9UX0FVVEhPUklaRUQQIRIOCgpQS0lfRkFJTEVEECISFgoSUEtJX1VOS05PV05fUFVCS0VZECMSGQoVQURNSU5fQkFEX1NFU1NJT05fS0VZECQSIQodQURNSU5fUFVCTElDX0tFWV9VTkFVVEhPUklaRUQQJUIJCgd2YXJpYW50IssBCgREYXRhEiQKB3BvcnRudW0YASABKA4yEy5tZXNodGFzdGljLlBvcnROdW0SDwoHcGF5bG9hZBgCIAEoDBIVCg13YW50X3Jlc3BvbnNlGAMgASgIEgwKBGRlc3QYBCABKAcSDgoGc291cmNlGAUgASgHEhIKCnJlcXVlc3RfaWQYBiABKAcSEAoIcmVwbHlfaWQYByABKAcSDQoFZW1vamkYCCABKAcSFQoIYml0ZmllbGQYCSABKA1IAIgBAUILCglfYml0ZmllbGQiPgoPS2V5VmVyaWZpY2F0aW9uEg0KBW5vbmNlGAEgASgEEg0KBWhhc2gxGAIgASgMEg0KBWhhc2gyGAMgASgMIrwBCghXYXlwb2ludBIKCgJpZBgBIAEoDRIXCgpsYXRpdHVkZV9pGAIgASgPSACIAQESGAoLbG9uZ2l0dWRlX2kYAyABKA9IAYgBARIOCgZleHBpcmUYBCABKA0SEQoJbG9ja2VkX3RvGAUgASgNEgwKBG5hbWUYBiABKAkSEwoLZGVzY3JpcHRpb24YByABKAkSDAoEaWNvbhgIIAEoB0INCgtfbGF0aXR1ZGVfaUIOCgxfbG9uZ2l0dWRlX2kibAoWTXF0dENsaWVudFByb3h5TWVzc2FnZRINCgV0b3BpYxgBIAEoCRIOCgRkYXRhGAIgASgMSAASDgoEdGV4dBgDIAEoCUgAEhAKCHJldGFpbmVkGAQgASgIQhEKD3BheWxvYWRfdmFyaWFudCKbBQoKTWVzaFBhY2tldBIMCgRmcm9tGAEgASgHEgoKAnRvGAIgASgHEg8KB2NoYW5uZWwYAyABKA0SIwoHZGVjb2RlZBgEIAEoCzIQLm1lc2h0YXN0aWMuRGF0YUgAEhMKCWVuY3J5cHRlZBgFIAEoDEgAEgoKAmlkGAYgASgHEg8KB3J4X3RpbWUYByABKAcSDgoGcnhfc25yGAggASgCEhEKCWhvcF9saW1pdBgJIAEoDRIQCgh3YW50X2FjaxgKIAEoCBIxCghwcmlvcml0eRgLIAEoDjIfLm1lc2h0YXN0aWMuTWVzaFBhY2tldC5Qcmlvcml0eRIPCgdyeF9yc3NpGAwgASgFEjMKB2RlbGF5ZWQYDSABKA4yHi5tZXNodGFzdGljLk1lc2hQYWNrZXQuRGVsYXllZEICGAESEAoIdmlhX21xdHQYDiABKAgSEQoJaG9wX3N0YXJ0GA8gASgNEhIKCnB1YmxpY19rZXkYECABKAwSFQoNcGtpX2VuY3J5cHRlZBgRIAEoCBIQCghuZXh0X2hvcBgSIAEoDRISCgpyZWxheV9ub2RlGBMgASgNEhAKCHR4X2FmdGVyGBQgASgNIn4KCFByaW9yaXR5EgkKBVVOU0VUEAASBwoDTUlOEAESDgoKQkFDS0dST1VORBAKEgsKB0RFRkFVTFQQQBIMCghSRUxJQUJMRRBGEgwKCFJFU1BPTlNFEFASCAoESElHSBBkEgkKBUFMRVJUEG4SBwoDQUNLEHgSBwoDTUFYEH8iQgoHRGVsYXllZBIMCghOT19ERUxBWRAAEhUKEURFTEFZRURfQlJPQURDQVNUEAESEgoOREVMQVlFRF9ESVJFQ1QQAkIRCg9wYXlsb2FkX3ZhcmlhbnQixwIKCE5vZGVJbmZvEgsKA251bRgBIAEoDRIeCgR1c2VyGAIgASgLMhAubWVzaHRhc3RpYy5Vc2VyEiYKCHBvc2l0aW9uGAMgASgLMhQubWVzaHRhc3RpYy5Qb3NpdGlvbhILCgNzbnIYBCABKAISEgoKbGFzdF9oZWFyZBgFIAEoBxIxCg5kZXZpY2VfbWV0cmljcxgGIAEoCzIZLm1lc2h0YXN0aWMuRGV2aWNlTWV0cmljcxIPCgdjaGFubmVsGAcgASgNEhAKCHZpYV9tcXR0GAggASgIEhYKCWhvcHNfYXdheRgJIAEoDUgAiAEBEhMKC2lzX2Zhdm9yaXRlGAogASgIEhIKCmlzX2lnbm9yZWQYCyABKAgSIAoYaXNfa2V5X21hbnVhbGx5X3ZlcmlmaWVkGAwgASgIQgwKCl9ob3BzX2F3YXkidAoKTXlOb2RlSW5mbxITCgtteV9ub2RlX251bRgBIAEoDRIUCgxyZWJvb3RfY291bnQYCCABKA0SFwoPbWluX2FwcF92ZXJzaW9uGAsgASgNEhEKCWRldmljZV9pZBgMIAEoDBIPCgdwaW9fZW52GA0gASgJIsABCglMb2dSZWNvcmQSDwoHbWVzc2FnZRgBIAEoCRIMCgR0aW1lGAIgASgHEg4KBnNvdXJjZRgDIAEoCRIqCgVsZXZlbBgEIAEoDjIbLm1lc2h0YXN0aWMuTG9nUmVjb3JkLkxldmVsIlgKBUxldmVsEgkKBVVOU0VUEAASDAoIQ1JJVElDQUwQMhIJCgVFUlJPUhAoEgsKB1dBUk5JTkcQHhIICgRJTkZPEBQSCQoFREVCVUcQChIJCgVUUkFDRRAFIlAKC1F1ZXVlU3RhdHVzEgsKA3JlcxgBIAEoBRIMCgRmcmVlGAIgASgNEg4KBm1heGxlbhgDIAEoDRIWCg5tZXNoX3BhY2tldF9pZBgEIAEoDSL5BQoJRnJvbVJhZGlvEgoKAmlkGAEgASgNEigKBnBhY2tldBgCIAEoCzIWLm1lc2h0YXN0aWMuTWVzaFBhY2tldEgAEikKB215X2luZm8YAyABKAsyFi5tZXNodGFzdGljLk15Tm9kZUluZm9IABIpCglub2RlX2luZm8YBCABKAsyFC5tZXNodGFzdGljLk5vZGVJbmZvSAASJAoGY29uZmlnGAUgASgLMhIubWVzaHRhc3RpYy5Db25maWdIABIrCgpsb2dfcmVjb3JkGAYgASgLMhUubWVzaHRhc3RpYy5Mb2dSZWNvcmRIABIcChJjb25maWdfY29tcGxldGVfaWQYByABKA1IABISCghyZWJvb3RlZBgIIAEoCEgAEjAKDG1vZHVsZUNvbmZpZxgJIAEoCzIYLm1lc2h0YXN0aWMuTW9kdWxlQ29uZmlnSAASJgoHY2hhbm5lbBgKIAEoCzITLm1lc2h0YXN0aWMuQ2hhbm5lbEgAEi4KC3F1ZXVlU3RhdHVzGAsgASgLMhcubWVzaHRhc3RpYy5RdWV1ZVN0YXR1c0gAEioKDHhtb2RlbVBhY2tldBgMIAEoCzISLm1lc2h0YXN0aWMuWE1vZGVtSAASLgoIbWV0YWRhdGEYDSABKAsyGi5tZXNodGFzdGljLkRldmljZU1ldGFkYXRhSAASRAoWbXF0dENsaWVudFByb3h5TWVzc2FnZRgOIAEoCzIiLm1lc2h0YXN0aWMuTXF0dENsaWVudFByb3h5TWVzc2FnZUgAEigKCGZpbGVJbmZvGA8gASgLMhQubWVzaHRhc3RpYy5GaWxlSW5mb0gAEjwKEmNsaWVudE5vdGlmaWNhdGlvbhgQIAEoCzIeLm1lc2h0YXN0aWMuQ2xpZW50Tm90aWZpY2F0aW9uSAASNAoOZGV2aWNldWlDb25maWcYESABKAsyGi5tZXNodGFzdGljLkRldmljZVVJQ29uZmlnSABCEQoPcGF5bG9hZF92YXJpYW50IvoDChJDbGllbnROb3RpZmljYXRpb24SFQoIcmVwbHlfaWQYASABKA1IAYgBARIMCgR0aW1lGAIgASgHEioKBWxldmVsGAMgASgOMhsubWVzaHRhc3RpYy5Mb2dSZWNvcmQuTGV2ZWwSDwoHbWVzc2FnZRgEIAEoCRJRCh5rZXlfdmVyaWZpY2F0aW9uX251bWJlcl9pbmZvcm0YCyABKAsyJy5tZXNodGFzdGljLktleVZlcmlmaWNhdGlvbk51bWJlckluZm9ybUgAElMKH2tleV92ZXJpZmljYXRpb25fbnVtYmVyX3JlcXVlc3QYDCABKAsyKC5tZXNodGFzdGljLktleVZlcmlmaWNhdGlvbk51bWJlclJlcXVlc3RIABJCChZrZXlfdmVyaWZpY2F0aW9uX2ZpbmFsGA0gASgLMiAubWVzaHRhc3RpYy5LZXlWZXJpZmljYXRpb25GaW5hbEgAEkAKFWR1cGxpY2F0ZWRfcHVibGljX2tleRgOIAEoCzIfLm1lc2h0YXN0aWMuRHVwbGljYXRlZFB1YmxpY0tleUgAEjQKD2xvd19lbnRyb3B5X2tleRgPIAEoCzIZLm1lc2h0YXN0aWMuTG93RW50cm9weUtleUgAQhEKD3BheWxvYWRfdmFyaWFudEILCglfcmVwbHlfaWQiXgobS2V5VmVyaWZpY2F0aW9uTnVtYmVySW5mb3JtEg0KBW5vbmNlGAEgASgEEhcKD3JlbW90ZV9sb25nbmFtZRgCIAEoCRIXCg9zZWN1cml0eV9udW1iZXIYAyABKA0iRgocS2V5VmVyaWZpY2F0aW9uTnVtYmVyUmVxdWVzdBINCgVub25jZRgBIAEoBBIXCg9yZW1vdGVfbG9uZ25hbWUYAiABKAkicQoUS2V5VmVyaWZpY2F0aW9uRmluYWwSDQoFbm9uY2UYASABKAQSFwoPcmVtb3RlX2xvbmduYW1lGAIgASgJEhAKCGlzU2VuZGVyGAMgASgIEh8KF3ZlcmlmaWNhdGlvbl9jaGFyYWN0ZXJzGAQgASgJIhUKE0R1cGxpY2F0ZWRQdWJsaWNLZXkiDwoNTG93RW50cm9weUtleSIxCghGaWxlSW5mbxIRCglmaWxlX25hbWUYASABKAkSEgoKc2l6ZV9ieXRlcxgCIAEoDSKUAgoHVG9SYWRpbxIoCgZwYWNrZXQYASABKAsyFi5tZXNodGFzdGljLk1lc2hQYWNrZXRIABIYCg53YW50X2NvbmZpZ19pZBgDIAEoDUgAEhQKCmRpc2Nvbm5lY3QYBCABKAhIABIqCgx4bW9kZW1QYWNrZXQYBSABKAsyEi5tZXNodGFzdGljLlhNb2RlbUgAEkQKFm1xdHRDbGllbnRQcm94eU1lc3NhZ2UYBiABKAsyIi5tZXNodGFzdGljLk1xdHRDbGllbnRQcm94eU1lc3NhZ2VIABIqCgloZWFydGJlYXQYByABKAsyFS5tZXNodGFzdGljLkhlYXJ0YmVhdEgAQhEKD3BheWxvYWRfdmFyaWFudCJACgpDb21wcmVzc2VkEiQKB3BvcnRudW0YASABKA4yEy5tZXNodGFzdGljLlBvcnROdW0SDAoEZGF0YRgCIAEoDCKHAQoMTmVpZ2hib3JJbmZvEg8KB25vZGVfaWQYASABKA0SFwoPbGFzdF9zZW50X2J5X2lkGAIgASgNEiQKHG5vZGVfYnJvYWRjYXN0X2ludGVydmFsX3NlY3MYAyABKA0SJwoJbmVpZ2hib3JzGAQgAygLMhQubWVzaHRhc3RpYy5OZWlnaGJvciJkCghOZWlnaGJvchIPCgdub2RlX2lkGAEgASgNEgsKA3NuchgCIAEoAhIUCgxsYXN0X3J4X3RpbWUYAyABKAcSJAocbm9kZV9icm9hZGNhc3RfaW50ZXJ2YWxfc2VjcxgEIAEoDSLXAgoORGV2aWNlTWV0YWRhdGESGAoQZmlybXdhcmVfdmVyc2lvbhgBIAEoCRIcChRkZXZpY2Vfc3RhdGVfdmVyc2lvbhgCIAEoDRITCgtjYW5TaHV0ZG93bhgDIAEoCBIPCgdoYXNXaWZpGAQgASgIEhQKDGhhc0JsdWV0b290aBgFIAEoCBITCgtoYXNFdGhlcm5ldBgGIAEoCBIyCgRyb2xlGAcgASgOMiQubWVzaHRhc3RpYy5Db25maWcuRGV2aWNlQ29uZmlnLlJvbGUSFgoOcG9zaXRpb25fZmxhZ3MYCCABKA0SKwoIaHdfbW9kZWwYCSABKA4yGS5tZXNodGFzdGljLkhhcmR3YXJlTW9kZWwSGQoRaGFzUmVtb3RlSGFyZHdhcmUYCiABKAgSDgoGaGFzUEtDGAsgASgIEhgKEGV4Y2x1ZGVkX21vZHVsZXMYDCABKA0iCwoJSGVhcnRiZWF0IlUKFU5vZGVSZW1vdGVIYXJkd2FyZVBpbhIQCghub2RlX251bRgBIAEoDRIqCgNwaW4YAiABKAsyHS5tZXNodGFzdGljLlJlbW90ZUhhcmR3YXJlUGluImUKDkNodW5rZWRQYXlsb2FkEhIKCnBheWxvYWRfaWQYASABKA0SEwoLY2h1bmtfY291bnQYAiABKA0SEwoLY2h1bmtfaW5kZXgYAyABKA0SFQoNcGF5bG9hZF9jaHVuaxgEIAEoDCIfCg1yZXNlbmRfY2h1bmtzEg4KBmNodW5rcxgBIAMoDSKqAQoWQ2h1bmtlZFBheWxvYWRSZXNwb25zZRISCgpwYXlsb2FkX2lkGAEgASgNEhoKEHJlcXVlc3RfdHJhbnNmZXIYAiABKAhIABIZCg9hY2NlcHRfdHJhbnNmZXIYAyABKAhIABIyCg1yZXNlbmRfY2h1bmtzGAQgASgLMhkubWVzaHRhc3RpYy5yZXNlbmRfY2h1bmtzSABCEQoPcGF5bG9hZF92YXJpYW50KvcPCg1IYXJkd2FyZU1vZGVsEgkKBVVOU0VUEAASDAoIVExPUkFfVjIQARIMCghUTE9SQV9WMRACEhIKDlRMT1JBX1YyXzFfMVA2EAMSCQoFVEJFQU0QBBIPCgtIRUxURUNfVjJfMBAFEg4KClRCRUFNX1YwUDcQBhIKCgZUX0VDSE8QBxIQCgxUTE9SQV9WMV8xUDMQCBILCgdSQUs0NjMxEAkSDwoLSEVMVEVDX1YyXzEQChINCglIRUxURUNfVjEQCxIYChRMSUxZR09fVEJFQU1fUzNfQ09SRRAMEgwKCFJBSzExMjAwEA0SCwoHTkFOT19HMRAOEhIKDlRMT1JBX1YyXzFfMVA4EA8SDwoLVExPUkFfVDNfUzMQEBIUChBOQU5PX0cxX0VYUExPUkVSEBESEQoNTkFOT19HMl9VTFRSQRASEg0KCUxPUkFfVFlQRRATEgsKB1dJUEhPTkUQFBIOCgpXSU9fV00xMTEwEBUSCwoHUkFLMjU2MBAWEhMKD0hFTFRFQ19IUlVfMzYwMRAXEhoKFkhFTFRFQ19XSVJFTEVTU19CUklER0UQGBIOCgpTVEFUSU9OX0cxEBkSDAoIUkFLMTEzMTAQGhIUChBTRU5TRUxPUkFfUlAyMDQwEBsSEAoMU0VOU0VMT1JBX1MzEBwSDQoJQ0FOQVJZT05FEB0SDwoLUlAyMDQwX0xPUkEQHhIOCgpTVEFUSU9OX0cyEB8SEQoNTE9SQV9SRUxBWV9WMRAgEg4KCk5SRjUyODQwREsQIRIHCgNQUFIQIhIPCgtHRU5JRUJMT0NLUxAjEhEKDU5SRjUyX1VOS05PV04QJBINCglQT1JURFVJTk8QJRIPCgtBTkRST0lEX1NJTRAmEgoKBkRJWV9WMRAnEhUKEU5SRjUyODQwX1BDQTEwMDU5ECgSCgoGRFJfREVWECkSCwoHTTVTVEFDSxAqEg0KCUhFTFRFQ19WMxArEhEKDUhFTFRFQ19XU0xfVjMQLBITCg9CRVRBRlBWXzI0MDBfVFgQLRIXChNCRVRBRlBWXzkwMF9OQU5PX1RYEC4SDAoIUlBJX1BJQ08QLxIbChdIRUxURUNfV0lSRUxFU1NfVFJBQ0tFUhAwEhkKFUhFTFRFQ19XSVJFTEVTU19QQVBFUhAxEgoKBlRfREVDSxAyEg4KClRfV0FUQ0hfUzMQMxIRCg1QSUNPTVBVVEVSX1MzEDQSDwoLSEVMVEVDX0hUNjIQNRISCg5FQllURV9FU1AzMl9TMxA2EhEKDUVTUDMyX1MzX1BJQ08QNxINCglDSEFUVEVSXzIQOBIeChpIRUxURUNfV0lSRUxFU1NfUEFQRVJfVjFfMBA5EiAKHEhFTFRFQ19XSVJFTEVTU19UUkFDS0VSX1YxXzAQOhILCgdVTlBIT05FEDsSDAoIVERfTE9SQUMQPBITCg9DREVCWVRFX0VPUkFfUzMQPRIPCgtUV0NfTUVTSF9WNBA+EhYKEk5SRjUyX1BST01JQ1JPX0RJWRA/Eh8KG1JBRElPTUFTVEVSXzkwMF9CQU5ESVRfTkFOTxBAEhwKGEhFTFRFQ19DQVBTVUxFX1NFTlNPUl9WMxBBEh0KGUhFTFRFQ19WSVNJT05fTUFTVEVSX1QxOTAQQhIdChlIRUxURUNfVklTSU9OX01BU1RFUl9FMjEzEEMSHQoZSEVMVEVDX1ZJU0lPTl9NQVNURVJfRTI5MBBEEhkKFUhFTFRFQ19NRVNIX05PREVfVDExNBBFEhYKElNFTlNFQ0FQX0lORElDQVRPUhBGEhMKD1RSQUNLRVJfVDEwMDBfRRBHEgsKB1JBSzMxNzIQSBIKCgZXSU9fRTUQSRIaChZSQURJT01BU1RFUl85MDBfQkFORElUEEoSEwoPTUUyNUxTMDFfNFkxMFREEEsSGAoUUlAyMDQwX0ZFQVRIRVJfUkZNOTUQTBIVChFNNVNUQUNLX0NPUkVCQVNJQxBNEhEKDU01U1RBQ0tfQ09SRTIQThINCglSUElfUElDTzIQTxISCg5NNVNUQUNLX0NPUkVTMxBQEhEKDVNFRUVEX1hJQU9fUzMQURILCgdNUzI0U0YxEFISDAoIVExPUkFfQzYQUxIPCgtXSVNNRVNIX1RBUBBUEg0KCVJPVVRBU1RJQxBVEgwKCE1FU0hfVEFCEFYSDAoITUVTSExJTksQVxISCg5YSUFPX05SRjUyX0tJVBBYEhAKDFRISU5LTk9ERV9NMRBZEhAKDFRISU5LTk9ERV9NMhBaEg8KC1RfRVRIX0VMSVRFEFsSFQoRSEVMVEVDX1NFTlNPUl9IVUIQXBIaChZSRVNFUlZFRF9GUklFRF9DSElDS0VOEF0SFgoSSEVMVEVDX01FU0hfUE9DS0VUEF4SFAoQU0VFRURfU09MQVJfTk9ERRBfEhgKFE5PTUFEU1RBUl9NRVRFT1JfUFJPEGASDQoJQ1JPV1BBTkVMEGESCwoHTElOS18zMhBiEhgKFFNFRUVEX1dJT19UUkFDS0VSX0wxEGMSHQoZU0VFRURfV0lPX1RSQUNLRVJfTDFfRUlOSxBkEhQKEFFXQU5UWl9USU5ZX0FSTVMQZRIOCgpUX0RFQ0tfUFJPEGYSEAoMVF9MT1JBX1BBR0VSEGcSHQoZR0FUNTYyX01FU0hfVFJJQUxfVFJBQ0tFUhBoEg8KClBSSVZBVEVfSFcQ/wEqLAoJQ29uc3RhbnRzEggKBFpFUk8QABIVChBEQVRBX1BBWUxPQURfTEVOEOkBKrQCChFDcml0aWNhbEVycm9yQ29kZRIICgROT05FEAASDwoLVFhfV0FUQ0hET0cQARIUChBTTEVFUF9FTlRFUl9XQUlUEAISDAoITk9fUkFESU8QAxIPCgtVTlNQRUNJRklFRBAEEhUKEVVCTE9YX1VOSVRfRkFJTEVEEAUSDQoJTk9fQVhQMTkyEAYSGQoVSU5WQUxJRF9SQURJT19TRVRUSU5HEAcSEwoPVFJBTlNNSVRfRkFJTEVEEAgSDAoIQlJPV05PVVQQCRISCg5TWDEyNjJfRkFJTFVSRRAKEhEKDVJBRElPX1NQSV9CVUcQCxIgChxGTEFTSF9DT1JSVVBUSU9OX1JFQ09WRVJBQkxFEAwSIgoeRkxBU0hfQ09SUlVQVElPTl9VTlJFQ09WRVJBQkxFEA0qgAMKD0V4Y2x1ZGVkTW9kdWxlcxIRCg1FWENMVURFRF9OT05FEAASDwoLTVFUVF9DT05GSUcQARIRCg1TRVJJQUxfQ09ORklHEAISEwoPRVhUTk9USUZfQ09ORklHEAQSFwoTU1RPUkVGT1JXQVJEX0NPTkZJRxAIEhQKEFJBTkdFVEVTVF9DT05GSUcQEBIUChBURUxFTUVUUllfQ09ORklHECASFAoQQ0FOTkVETVNHX0NPTkZJRxBAEhEKDEFVRElPX0NPTkZJRxCAARIaChVSRU1PVEVIQVJEV0FSRV9DT05GSUcQgAISGAoTTkVJR0hCT1JJTkZPX0NPTkZJRxCABBIbChZBTUJJRU5UTElHSFRJTkdfQ09ORklHEIAIEhsKFkRFVEVDVElPTlNFTlNPUl9DT05GSUcQgBASFgoRUEFYQ09VTlRFUl9DT05GSUcQgCASFQoQQkxVRVRPT1RIX0NPTkZJRxCAQBIUCg5ORVRXT1JLX0NPTkZJRxCAgAFCXwoTY29tLmdlZWtzdmlsbGUubWVzaEIKTWVzaFByb3Rvc1oiZ2l0aHViLmNvbS9tZXNodGFzdGljL2dvL2dlbmVyYXRlZKoCFE1lc2h0YXN0aWMuUHJvdG9idWZzugIAYgZwcm90bzM", [
	Po,
	O,
	k,
	fc,
	j,
	Dc,
	Bo
]), jc = /* @__PURE__ */ E(P, 0), Mc = /* @__PURE__ */ function(e) {
	return e[e.LOC_UNSET = 0] = "LOC_UNSET", e[e.LOC_MANUAL = 1] = "LOC_MANUAL", e[e.LOC_INTERNAL = 2] = "LOC_INTERNAL", e[e.LOC_EXTERNAL = 3] = "LOC_EXTERNAL", e;
}({}), Nc = /* @__PURE__ */ C(P, 0, 0), Pc = /* @__PURE__ */ function(e) {
	return e[e.ALT_UNSET = 0] = "ALT_UNSET", e[e.ALT_MANUAL = 1] = "ALT_MANUAL", e[e.ALT_INTERNAL = 2] = "ALT_INTERNAL", e[e.ALT_EXTERNAL = 3] = "ALT_EXTERNAL", e[e.ALT_BAROMETRIC = 4] = "ALT_BAROMETRIC", e;
}({}), Fc = /* @__PURE__ */ C(P, 0, 1), Ic = /* @__PURE__ */ E(P, 1), Lc = /* @__PURE__ */ E(P, 2), Rc = /* @__PURE__ */ E(P, 3), zc = /* @__PURE__ */ function(e) {
	return e[e.NONE = 0] = "NONE", e[e.NO_ROUTE = 1] = "NO_ROUTE", e[e.GOT_NAK = 2] = "GOT_NAK", e[e.TIMEOUT = 3] = "TIMEOUT", e[e.NO_INTERFACE = 4] = "NO_INTERFACE", e[e.MAX_RETRANSMIT = 5] = "MAX_RETRANSMIT", e[e.NO_CHANNEL = 6] = "NO_CHANNEL", e[e.TOO_LARGE = 7] = "TOO_LARGE", e[e.NO_RESPONSE = 8] = "NO_RESPONSE", e[e.DUTY_CYCLE_LIMIT = 9] = "DUTY_CYCLE_LIMIT", e[e.BAD_REQUEST = 32] = "BAD_REQUEST", e[e.NOT_AUTHORIZED = 33] = "NOT_AUTHORIZED", e[e.PKI_FAILED = 34] = "PKI_FAILED", e[e.PKI_UNKNOWN_PUBKEY = 35] = "PKI_UNKNOWN_PUBKEY", e[e.ADMIN_BAD_SESSION_KEY = 36] = "ADMIN_BAD_SESSION_KEY", e[e.ADMIN_PUBLIC_KEY_UNAUTHORIZED = 37] = "ADMIN_PUBLIC_KEY_UNAUTHORIZED", e;
}({}), Bc = /* @__PURE__ */ C(P, 3, 0), Vc = /* @__PURE__ */ E(P, 4), Hc = /* @__PURE__ */ E(P, 5), Uc = /* @__PURE__ */ E(P, 6), Wc = /* @__PURE__ */ E(P, 7), Gc = /* @__PURE__ */ E(P, 8), Kc = /* @__PURE__ */ function(e) {
	return e[e.UNSET = 0] = "UNSET", e[e.MIN = 1] = "MIN", e[e.BACKGROUND = 10] = "BACKGROUND", e[e.DEFAULT = 64] = "DEFAULT", e[e.RELIABLE = 70] = "RELIABLE", e[e.RESPONSE = 80] = "RESPONSE", e[e.HIGH = 100] = "HIGH", e[e.ALERT = 110] = "ALERT", e[e.ACK = 120] = "ACK", e[e.MAX = 127] = "MAX", e;
}({}), qc = /* @__PURE__ */ C(P, 8, 0), Jc = /* @__PURE__ */ function(e) {
	return e[e.NO_DELAY = 0] = "NO_DELAY", e[e.DELAYED_BROADCAST = 1] = "DELAYED_BROADCAST", e[e.DELAYED_DIRECT = 2] = "DELAYED_DIRECT", e;
}({}), Yc = /* @__PURE__ */ C(P, 8, 1), Xc = /* @__PURE__ */ E(P, 9), Zc = /* @__PURE__ */ E(P, 10), Qc = /* @__PURE__ */ E(P, 11), $c = /* @__PURE__ */ function(e) {
	return e[e.UNSET = 0] = "UNSET", e[e.CRITICAL = 50] = "CRITICAL", e[e.ERROR = 40] = "ERROR", e[e.WARNING = 30] = "WARNING", e[e.INFO = 20] = "INFO", e[e.DEBUG = 10] = "DEBUG", e[e.TRACE = 5] = "TRACE", e;
}({}), el = /* @__PURE__ */ C(P, 11, 0), tl = /* @__PURE__ */ E(P, 12), nl = /* @__PURE__ */ E(P, 13), rl = /* @__PURE__ */ E(P, 14), il = /* @__PURE__ */ E(P, 15), al = /* @__PURE__ */ E(P, 16), ol = /* @__PURE__ */ E(P, 17), sl = /* @__PURE__ */ E(P, 18), cl = /* @__PURE__ */ E(P, 19), ll = /* @__PURE__ */ E(P, 20), ul = /* @__PURE__ */ E(P, 21), dl = /* @__PURE__ */ E(P, 22), fl = /* @__PURE__ */ E(P, 23), pl = /* @__PURE__ */ E(P, 24), ml = /* @__PURE__ */ E(P, 25), hl = /* @__PURE__ */ E(P, 26), gl = /* @__PURE__ */ E(P, 27), _l = /* @__PURE__ */ E(P, 28), vl = /* @__PURE__ */ E(P, 29), yl = /* @__PURE__ */ E(P, 30), bl = /* @__PURE__ */ function(e) {
	return e[e.UNSET = 0] = "UNSET", e[e.TLORA_V2 = 1] = "TLORA_V2", e[e.TLORA_V1 = 2] = "TLORA_V1", e[e.TLORA_V2_1_1P6 = 3] = "TLORA_V2_1_1P6", e[e.TBEAM = 4] = "TBEAM", e[e.HELTEC_V2_0 = 5] = "HELTEC_V2_0", e[e.TBEAM_V0P7 = 6] = "TBEAM_V0P7", e[e.T_ECHO = 7] = "T_ECHO", e[e.TLORA_V1_1P3 = 8] = "TLORA_V1_1P3", e[e.RAK4631 = 9] = "RAK4631", e[e.HELTEC_V2_1 = 10] = "HELTEC_V2_1", e[e.HELTEC_V1 = 11] = "HELTEC_V1", e[e.LILYGO_TBEAM_S3_CORE = 12] = "LILYGO_TBEAM_S3_CORE", e[e.RAK11200 = 13] = "RAK11200", e[e.NANO_G1 = 14] = "NANO_G1", e[e.TLORA_V2_1_1P8 = 15] = "TLORA_V2_1_1P8", e[e.TLORA_T3_S3 = 16] = "TLORA_T3_S3", e[e.NANO_G1_EXPLORER = 17] = "NANO_G1_EXPLORER", e[e.NANO_G2_ULTRA = 18] = "NANO_G2_ULTRA", e[e.LORA_TYPE = 19] = "LORA_TYPE", e[e.WIPHONE = 20] = "WIPHONE", e[e.WIO_WM1110 = 21] = "WIO_WM1110", e[e.RAK2560 = 22] = "RAK2560", e[e.HELTEC_HRU_3601 = 23] = "HELTEC_HRU_3601", e[e.HELTEC_WIRELESS_BRIDGE = 24] = "HELTEC_WIRELESS_BRIDGE", e[e.STATION_G1 = 25] = "STATION_G1", e[e.RAK11310 = 26] = "RAK11310", e[e.SENSELORA_RP2040 = 27] = "SENSELORA_RP2040", e[e.SENSELORA_S3 = 28] = "SENSELORA_S3", e[e.CANARYONE = 29] = "CANARYONE", e[e.RP2040_LORA = 30] = "RP2040_LORA", e[e.STATION_G2 = 31] = "STATION_G2", e[e.LORA_RELAY_V1 = 32] = "LORA_RELAY_V1", e[e.NRF52840DK = 33] = "NRF52840DK", e[e.PPR = 34] = "PPR", e[e.GENIEBLOCKS = 35] = "GENIEBLOCKS", e[e.NRF52_UNKNOWN = 36] = "NRF52_UNKNOWN", e[e.PORTDUINO = 37] = "PORTDUINO", e[e.ANDROID_SIM = 38] = "ANDROID_SIM", e[e.DIY_V1 = 39] = "DIY_V1", e[e.NRF52840_PCA10059 = 40] = "NRF52840_PCA10059", e[e.DR_DEV = 41] = "DR_DEV", e[e.M5STACK = 42] = "M5STACK", e[e.HELTEC_V3 = 43] = "HELTEC_V3", e[e.HELTEC_WSL_V3 = 44] = "HELTEC_WSL_V3", e[e.BETAFPV_2400_TX = 45] = "BETAFPV_2400_TX", e[e.BETAFPV_900_NANO_TX = 46] = "BETAFPV_900_NANO_TX", e[e.RPI_PICO = 47] = "RPI_PICO", e[e.HELTEC_WIRELESS_TRACKER = 48] = "HELTEC_WIRELESS_TRACKER", e[e.HELTEC_WIRELESS_PAPER = 49] = "HELTEC_WIRELESS_PAPER", e[e.T_DECK = 50] = "T_DECK", e[e.T_WATCH_S3 = 51] = "T_WATCH_S3", e[e.PICOMPUTER_S3 = 52] = "PICOMPUTER_S3", e[e.HELTEC_HT62 = 53] = "HELTEC_HT62", e[e.EBYTE_ESP32_S3 = 54] = "EBYTE_ESP32_S3", e[e.ESP32_S3_PICO = 55] = "ESP32_S3_PICO", e[e.CHATTER_2 = 56] = "CHATTER_2", e[e.HELTEC_WIRELESS_PAPER_V1_0 = 57] = "HELTEC_WIRELESS_PAPER_V1_0", e[e.HELTEC_WIRELESS_TRACKER_V1_0 = 58] = "HELTEC_WIRELESS_TRACKER_V1_0", e[e.UNPHONE = 59] = "UNPHONE", e[e.TD_LORAC = 60] = "TD_LORAC", e[e.CDEBYTE_EORA_S3 = 61] = "CDEBYTE_EORA_S3", e[e.TWC_MESH_V4 = 62] = "TWC_MESH_V4", e[e.NRF52_PROMICRO_DIY = 63] = "NRF52_PROMICRO_DIY", e[e.RADIOMASTER_900_BANDIT_NANO = 64] = "RADIOMASTER_900_BANDIT_NANO", e[e.HELTEC_CAPSULE_SENSOR_V3 = 65] = "HELTEC_CAPSULE_SENSOR_V3", e[e.HELTEC_VISION_MASTER_T190 = 66] = "HELTEC_VISION_MASTER_T190", e[e.HELTEC_VISION_MASTER_E213 = 67] = "HELTEC_VISION_MASTER_E213", e[e.HELTEC_VISION_MASTER_E290 = 68] = "HELTEC_VISION_MASTER_E290", e[e.HELTEC_MESH_NODE_T114 = 69] = "HELTEC_MESH_NODE_T114", e[e.SENSECAP_INDICATOR = 70] = "SENSECAP_INDICATOR", e[e.TRACKER_T1000_E = 71] = "TRACKER_T1000_E", e[e.RAK3172 = 72] = "RAK3172", e[e.WIO_E5 = 73] = "WIO_E5", e[e.RADIOMASTER_900_BANDIT = 74] = "RADIOMASTER_900_BANDIT", e[e.ME25LS01_4Y10TD = 75] = "ME25LS01_4Y10TD", e[e.RP2040_FEATHER_RFM95 = 76] = "RP2040_FEATHER_RFM95", e[e.M5STACK_COREBASIC = 77] = "M5STACK_COREBASIC", e[e.M5STACK_CORE2 = 78] = "M5STACK_CORE2", e[e.RPI_PICO2 = 79] = "RPI_PICO2", e[e.M5STACK_CORES3 = 80] = "M5STACK_CORES3", e[e.SEEED_XIAO_S3 = 81] = "SEEED_XIAO_S3", e[e.MS24SF1 = 82] = "MS24SF1", e[e.TLORA_C6 = 83] = "TLORA_C6", e[e.WISMESH_TAP = 84] = "WISMESH_TAP", e[e.ROUTASTIC = 85] = "ROUTASTIC", e[e.MESH_TAB = 86] = "MESH_TAB", e[e.MESHLINK = 87] = "MESHLINK", e[e.XIAO_NRF52_KIT = 88] = "XIAO_NRF52_KIT", e[e.THINKNODE_M1 = 89] = "THINKNODE_M1", e[e.THINKNODE_M2 = 90] = "THINKNODE_M2", e[e.T_ETH_ELITE = 91] = "T_ETH_ELITE", e[e.HELTEC_SENSOR_HUB = 92] = "HELTEC_SENSOR_HUB", e[e.RESERVED_FRIED_CHICKEN = 93] = "RESERVED_FRIED_CHICKEN", e[e.HELTEC_MESH_POCKET = 94] = "HELTEC_MESH_POCKET", e[e.SEEED_SOLAR_NODE = 95] = "SEEED_SOLAR_NODE", e[e.NOMADSTAR_METEOR_PRO = 96] = "NOMADSTAR_METEOR_PRO", e[e.CROWPANEL = 97] = "CROWPANEL", e[e.LINK_32 = 98] = "LINK_32", e[e.SEEED_WIO_TRACKER_L1 = 99] = "SEEED_WIO_TRACKER_L1", e[e.SEEED_WIO_TRACKER_L1_EINK = 100] = "SEEED_WIO_TRACKER_L1_EINK", e[e.QWANTZ_TINY_ARMS = 101] = "QWANTZ_TINY_ARMS", e[e.T_DECK_PRO = 102] = "T_DECK_PRO", e[e.T_LORA_PAGER = 103] = "T_LORA_PAGER", e[e.GAT562_MESH_TRIAL_TRACKER = 104] = "GAT562_MESH_TRIAL_TRACKER", e[e.PRIVATE_HW = 255] = "PRIVATE_HW", e;
}({}), xl = /* @__PURE__ */ C(P, 0), Sl = /* @__PURE__ */ function(e) {
	return e[e.ZERO = 0] = "ZERO", e[e.DATA_PAYLOAD_LEN = 233] = "DATA_PAYLOAD_LEN", e;
}({}), Cl = /* @__PURE__ */ C(P, 1), wl = /* @__PURE__ */ function(e) {
	return e[e.NONE = 0] = "NONE", e[e.TX_WATCHDOG = 1] = "TX_WATCHDOG", e[e.SLEEP_ENTER_WAIT = 2] = "SLEEP_ENTER_WAIT", e[e.NO_RADIO = 3] = "NO_RADIO", e[e.UNSPECIFIED = 4] = "UNSPECIFIED", e[e.UBLOX_UNIT_FAILED = 5] = "UBLOX_UNIT_FAILED", e[e.NO_AXP192 = 6] = "NO_AXP192", e[e.INVALID_RADIO_SETTING = 7] = "INVALID_RADIO_SETTING", e[e.TRANSMIT_FAILED = 8] = "TRANSMIT_FAILED", e[e.BROWNOUT = 9] = "BROWNOUT", e[e.SX1262_FAILURE = 10] = "SX1262_FAILURE", e[e.RADIO_SPI_BUG = 11] = "RADIO_SPI_BUG", e[e.FLASH_CORRUPTION_RECOVERABLE = 12] = "FLASH_CORRUPTION_RECOVERABLE", e[e.FLASH_CORRUPTION_UNRECOVERABLE = 13] = "FLASH_CORRUPTION_UNRECOVERABLE", e;
}({}), Tl = /* @__PURE__ */ C(P, 2), El = /* @__PURE__ */ function(e) {
	return e[e.EXCLUDED_NONE = 0] = "EXCLUDED_NONE", e[e.MQTT_CONFIG = 1] = "MQTT_CONFIG", e[e.SERIAL_CONFIG = 2] = "SERIAL_CONFIG", e[e.EXTNOTIF_CONFIG = 4] = "EXTNOTIF_CONFIG", e[e.STOREFORWARD_CONFIG = 8] = "STOREFORWARD_CONFIG", e[e.RANGETEST_CONFIG = 16] = "RANGETEST_CONFIG", e[e.TELEMETRY_CONFIG = 32] = "TELEMETRY_CONFIG", e[e.CANNEDMSG_CONFIG = 64] = "CANNEDMSG_CONFIG", e[e.AUDIO_CONFIG = 128] = "AUDIO_CONFIG", e[e.REMOTEHARDWARE_CONFIG = 256] = "REMOTEHARDWARE_CONFIG", e[e.NEIGHBORINFO_CONFIG = 512] = "NEIGHBORINFO_CONFIG", e[e.AMBIENTLIGHTING_CONFIG = 1024] = "AMBIENTLIGHTING_CONFIG", e[e.DETECTIONSENSOR_CONFIG = 2048] = "DETECTIONSENSOR_CONFIG", e[e.PAXCOUNTER_CONFIG = 4096] = "PAXCOUNTER_CONFIG", e[e.BLUETOOTH_CONFIG = 8192] = "BLUETOOTH_CONFIG", e[e.NETWORK_CONFIG = 16384] = "NETWORK_CONFIG", e;
}({}), Dl = /* @__PURE__ */ C(P, 3), F = g({
	AdminMessageSchema: () => kl,
	AdminMessage_BackupLocation: () => Fl,
	AdminMessage_BackupLocationSchema: () => Il,
	AdminMessage_ConfigType: () => jl,
	AdminMessage_ConfigTypeSchema: () => Ml,
	AdminMessage_InputEventSchema: () => Al,
	AdminMessage_ModuleConfigType: () => Nl,
	AdminMessage_ModuleConfigTypeSchema: () => Pl,
	HamParametersSchema: () => Ll,
	KeyVerificationAdminSchema: () => Bl,
	KeyVerificationAdmin_MessageType: () => Vl,
	KeyVerificationAdmin_MessageTypeSchema: () => Hl,
	NodeRemoteHardwarePinsResponseSchema: () => Rl,
	SharedContactSchema: () => zl,
	file_admin: () => Ol
}), Ol = /* @__PURE__ */ T("CgthZG1pbi5wcm90bxIKbWVzaHRhc3RpYyLWGAoMQWRtaW5NZXNzYWdlEhcKD3Nlc3Npb25fcGFzc2tleRhlIAEoDBIdChNnZXRfY2hhbm5lbF9yZXF1ZXN0GAEgASgNSAASMwoUZ2V0X2NoYW5uZWxfcmVzcG9uc2UYAiABKAsyEy5tZXNodGFzdGljLkNoYW5uZWxIABIbChFnZXRfb3duZXJfcmVxdWVzdBgDIAEoCEgAEi4KEmdldF9vd25lcl9yZXNwb25zZRgEIAEoCzIQLm1lc2h0YXN0aWMuVXNlckgAEkEKEmdldF9jb25maWdfcmVxdWVzdBgFIAEoDjIjLm1lc2h0YXN0aWMuQWRtaW5NZXNzYWdlLkNvbmZpZ1R5cGVIABIxChNnZXRfY29uZmlnX3Jlc3BvbnNlGAYgASgLMhIubWVzaHRhc3RpYy5Db25maWdIABJOChlnZXRfbW9kdWxlX2NvbmZpZ19yZXF1ZXN0GAcgASgOMikubWVzaHRhc3RpYy5BZG1pbk1lc3NhZ2UuTW9kdWxlQ29uZmlnVHlwZUgAEj4KGmdldF9tb2R1bGVfY29uZmlnX3Jlc3BvbnNlGAggASgLMhgubWVzaHRhc3RpYy5Nb2R1bGVDb25maWdIABI0CipnZXRfY2FubmVkX21lc3NhZ2VfbW9kdWxlX21lc3NhZ2VzX3JlcXVlc3QYCiABKAhIABI1CitnZXRfY2FubmVkX21lc3NhZ2VfbW9kdWxlX21lc3NhZ2VzX3Jlc3BvbnNlGAsgASgJSAASJQobZ2V0X2RldmljZV9tZXRhZGF0YV9yZXF1ZXN0GAwgASgISAASQgocZ2V0X2RldmljZV9tZXRhZGF0YV9yZXNwb25zZRgNIAEoCzIaLm1lc2h0YXN0aWMuRGV2aWNlTWV0YWRhdGFIABIeChRnZXRfcmluZ3RvbmVfcmVxdWVzdBgOIAEoCEgAEh8KFWdldF9yaW5ndG9uZV9yZXNwb25zZRgPIAEoCUgAEi4KJGdldF9kZXZpY2VfY29ubmVjdGlvbl9zdGF0dXNfcmVxdWVzdBgQIAEoCEgAElMKJWdldF9kZXZpY2VfY29ubmVjdGlvbl9zdGF0dXNfcmVzcG9uc2UYESABKAsyIi5tZXNodGFzdGljLkRldmljZUNvbm5lY3Rpb25TdGF0dXNIABIxCgxzZXRfaGFtX21vZGUYEiABKAsyGS5tZXNodGFzdGljLkhhbVBhcmFtZXRlcnNIABIvCiVnZXRfbm9kZV9yZW1vdGVfaGFyZHdhcmVfcGluc19yZXF1ZXN0GBMgASgISAASXAomZ2V0X25vZGVfcmVtb3RlX2hhcmR3YXJlX3BpbnNfcmVzcG9uc2UYFCABKAsyKi5tZXNodGFzdGljLk5vZGVSZW1vdGVIYXJkd2FyZVBpbnNSZXNwb25zZUgAEiAKFmVudGVyX2RmdV9tb2RlX3JlcXVlc3QYFSABKAhIABIdChNkZWxldGVfZmlsZV9yZXF1ZXN0GBYgASgJSAASEwoJc2V0X3NjYWxlGBcgASgNSAASRQoSYmFja3VwX3ByZWZlcmVuY2VzGBggASgOMicubWVzaHRhc3RpYy5BZG1pbk1lc3NhZ2UuQmFja3VwTG9jYXRpb25IABJGChNyZXN0b3JlX3ByZWZlcmVuY2VzGBkgASgOMicubWVzaHRhc3RpYy5BZG1pbk1lc3NhZ2UuQmFja3VwTG9jYXRpb25IABJMChlyZW1vdmVfYmFja3VwX3ByZWZlcmVuY2VzGBogASgOMicubWVzaHRhc3RpYy5BZG1pbk1lc3NhZ2UuQmFja3VwTG9jYXRpb25IABI/ChBzZW5kX2lucHV0X2V2ZW50GBsgASgLMiMubWVzaHRhc3RpYy5BZG1pbk1lc3NhZ2UuSW5wdXRFdmVudEgAEiUKCXNldF9vd25lchggIAEoCzIQLm1lc2h0YXN0aWMuVXNlckgAEioKC3NldF9jaGFubmVsGCEgASgLMhMubWVzaHRhc3RpYy5DaGFubmVsSAASKAoKc2V0X2NvbmZpZxgiIAEoCzISLm1lc2h0YXN0aWMuQ29uZmlnSAASNQoRc2V0X21vZHVsZV9jb25maWcYIyABKAsyGC5tZXNodGFzdGljLk1vZHVsZUNvbmZpZ0gAEiwKInNldF9jYW5uZWRfbWVzc2FnZV9tb2R1bGVfbWVzc2FnZXMYJCABKAlIABIeChRzZXRfcmluZ3RvbmVfbWVzc2FnZRglIAEoCUgAEhsKEXJlbW92ZV9ieV9ub2RlbnVtGCYgASgNSAASGwoRc2V0X2Zhdm9yaXRlX25vZGUYJyABKA1IABIeChRyZW1vdmVfZmF2b3JpdGVfbm9kZRgoIAEoDUgAEjIKEnNldF9maXhlZF9wb3NpdGlvbhgpIAEoCzIULm1lc2h0YXN0aWMuUG9zaXRpb25IABIfChVyZW1vdmVfZml4ZWRfcG9zaXRpb24YKiABKAhIABIXCg1zZXRfdGltZV9vbmx5GCsgASgHSAASHwoVZ2V0X3VpX2NvbmZpZ19yZXF1ZXN0GCwgASgISAASPAoWZ2V0X3VpX2NvbmZpZ19yZXNwb25zZRgtIAEoCzIaLm1lc2h0YXN0aWMuRGV2aWNlVUlDb25maWdIABI1Cg9zdG9yZV91aV9jb25maWcYLiABKAsyGi5tZXNodGFzdGljLkRldmljZVVJQ29uZmlnSAASGgoQc2V0X2lnbm9yZWRfbm9kZRgvIAEoDUgAEh0KE3JlbW92ZV9pZ25vcmVkX25vZGUYMCABKA1IABIdChNiZWdpbl9lZGl0X3NldHRpbmdzGEAgASgISAASHgoUY29tbWl0X2VkaXRfc2V0dGluZ3MYQSABKAhIABIwCgthZGRfY29udGFjdBhCIAEoCzIZLm1lc2h0YXN0aWMuU2hhcmVkQ29udGFjdEgAEjwKEGtleV92ZXJpZmljYXRpb24YQyABKAsyIC5tZXNodGFzdGljLktleVZlcmlmaWNhdGlvbkFkbWluSAASHgoUZmFjdG9yeV9yZXNldF9kZXZpY2UYXiABKAVIABIcChJyZWJvb3Rfb3RhX3NlY29uZHMYXyABKAVIABIYCg5leGl0X3NpbXVsYXRvchhgIAEoCEgAEhgKDnJlYm9vdF9zZWNvbmRzGGEgASgFSAASGgoQc2h1dGRvd25fc2Vjb25kcxhiIAEoBUgAEh4KFGZhY3RvcnlfcmVzZXRfY29uZmlnGGMgASgFSAASFgoMbm9kZWRiX3Jlc2V0GGQgASgFSAAaUwoKSW5wdXRFdmVudBISCgpldmVudF9jb2RlGAEgASgNEg8KB2tiX2NoYXIYAiABKA0SDwoHdG91Y2hfeBgDIAEoDRIPCgd0b3VjaF95GAQgASgNItYBCgpDb25maWdUeXBlEhEKDURFVklDRV9DT05GSUcQABITCg9QT1NJVElPTl9DT05GSUcQARIQCgxQT1dFUl9DT05GSUcQAhISCg5ORVRXT1JLX0NPTkZJRxADEhIKDkRJU1BMQVlfQ09ORklHEAQSDwoLTE9SQV9DT05GSUcQBRIUChBCTFVFVE9PVEhfQ09ORklHEAYSEwoPU0VDVVJJVFlfQ09ORklHEAcSFQoRU0VTU0lPTktFWV9DT05GSUcQCBITCg9ERVZJQ0VVSV9DT05GSUcQCSK7AgoQTW9kdWxlQ29uZmlnVHlwZRIPCgtNUVRUX0NPTkZJRxAAEhEKDVNFUklBTF9DT05GSUcQARITCg9FWFROT1RJRl9DT05GSUcQAhIXChNTVE9SRUZPUldBUkRfQ09ORklHEAMSFAoQUkFOR0VURVNUX0NPTkZJRxAEEhQKEFRFTEVNRVRSWV9DT05GSUcQBRIUChBDQU5ORURNU0dfQ09ORklHEAYSEAoMQVVESU9fQ09ORklHEAcSGQoVUkVNT1RFSEFSRFdBUkVfQ09ORklHEAgSFwoTTkVJR0hCT1JJTkZPX0NPTkZJRxAJEhoKFkFNQklFTlRMSUdIVElOR19DT05GSUcQChIaChZERVRFQ1RJT05TRU5TT1JfQ09ORklHEAsSFQoRUEFYQ09VTlRFUl9DT05GSUcQDCIjCg5CYWNrdXBMb2NhdGlvbhIJCgVGTEFTSBAAEgYKAlNEEAFCEQoPcGF5bG9hZF92YXJpYW50IlsKDUhhbVBhcmFtZXRlcnMSEQoJY2FsbF9zaWduGAEgASgJEhAKCHR4X3Bvd2VyGAIgASgFEhEKCWZyZXF1ZW5jeRgDIAEoAhISCgpzaG9ydF9uYW1lGAQgASgJImYKHk5vZGVSZW1vdGVIYXJkd2FyZVBpbnNSZXNwb25zZRJEChlub2RlX3JlbW90ZV9oYXJkd2FyZV9waW5zGAEgAygLMiEubWVzaHRhc3RpYy5Ob2RlUmVtb3RlSGFyZHdhcmVQaW4iWAoNU2hhcmVkQ29udGFjdBIQCghub2RlX251bRgBIAEoDRIeCgR1c2VyGAIgASgLMhAubWVzaHRhc3RpYy5Vc2VyEhUKDXNob3VsZF9pZ25vcmUYAyABKAginAIKFEtleVZlcmlmaWNhdGlvbkFkbWluEkIKDG1lc3NhZ2VfdHlwZRgBIAEoDjIsLm1lc2h0YXN0aWMuS2V5VmVyaWZpY2F0aW9uQWRtaW4uTWVzc2FnZVR5cGUSFgoOcmVtb3RlX25vZGVudW0YAiABKA0SDQoFbm9uY2UYAyABKAQSHAoPc2VjdXJpdHlfbnVtYmVyGAQgASgNSACIAQEiZwoLTWVzc2FnZVR5cGUSGQoVSU5JVElBVEVfVkVSSUZJQ0FUSU9OEAASGwoXUFJPVklERV9TRUNVUklUWV9OVU1CRVIQARINCglET19WRVJJRlkQAhIRCg1ET19OT1RfVkVSSUZZEANCEgoQX3NlY3VyaXR5X251bWJlckJgChNjb20uZ2Vla3N2aWxsZS5tZXNoQgtBZG1pblByb3Rvc1oiZ2l0aHViLmNvbS9tZXNodGFzdGljL2dvL2dlbmVyYXRlZKoCFE1lc2h0YXN0aWMuUHJvdG9idWZzugIAYgZwcm90bzM", [
	Po,
	O,
	As,
	P,
	k,
	Bo
]), kl = /* @__PURE__ */ E(Ol, 0), Al = /* @__PURE__ */ E(Ol, 0, 0), jl = /* @__PURE__ */ function(e) {
	return e[e.DEVICE_CONFIG = 0] = "DEVICE_CONFIG", e[e.POSITION_CONFIG = 1] = "POSITION_CONFIG", e[e.POWER_CONFIG = 2] = "POWER_CONFIG", e[e.NETWORK_CONFIG = 3] = "NETWORK_CONFIG", e[e.DISPLAY_CONFIG = 4] = "DISPLAY_CONFIG", e[e.LORA_CONFIG = 5] = "LORA_CONFIG", e[e.BLUETOOTH_CONFIG = 6] = "BLUETOOTH_CONFIG", e[e.SECURITY_CONFIG = 7] = "SECURITY_CONFIG", e[e.SESSIONKEY_CONFIG = 8] = "SESSIONKEY_CONFIG", e[e.DEVICEUI_CONFIG = 9] = "DEVICEUI_CONFIG", e;
}({}), Ml = /* @__PURE__ */ C(Ol, 0, 0), Nl = /* @__PURE__ */ function(e) {
	return e[e.MQTT_CONFIG = 0] = "MQTT_CONFIG", e[e.SERIAL_CONFIG = 1] = "SERIAL_CONFIG", e[e.EXTNOTIF_CONFIG = 2] = "EXTNOTIF_CONFIG", e[e.STOREFORWARD_CONFIG = 3] = "STOREFORWARD_CONFIG", e[e.RANGETEST_CONFIG = 4] = "RANGETEST_CONFIG", e[e.TELEMETRY_CONFIG = 5] = "TELEMETRY_CONFIG", e[e.CANNEDMSG_CONFIG = 6] = "CANNEDMSG_CONFIG", e[e.AUDIO_CONFIG = 7] = "AUDIO_CONFIG", e[e.REMOTEHARDWARE_CONFIG = 8] = "REMOTEHARDWARE_CONFIG", e[e.NEIGHBORINFO_CONFIG = 9] = "NEIGHBORINFO_CONFIG", e[e.AMBIENTLIGHTING_CONFIG = 10] = "AMBIENTLIGHTING_CONFIG", e[e.DETECTIONSENSOR_CONFIG = 11] = "DETECTIONSENSOR_CONFIG", e[e.PAXCOUNTER_CONFIG = 12] = "PAXCOUNTER_CONFIG", e;
}({}), Pl = /* @__PURE__ */ C(Ol, 0, 1), Fl = /* @__PURE__ */ function(e) {
	return e[e.FLASH = 0] = "FLASH", e[e.SD = 1] = "SD", e;
}({}), Il = /* @__PURE__ */ C(Ol, 0, 2), Ll = /* @__PURE__ */ E(Ol, 1), Rl = /* @__PURE__ */ E(Ol, 2), zl = /* @__PURE__ */ E(Ol, 3), Bl = /* @__PURE__ */ E(Ol, 4), Vl = /* @__PURE__ */ function(e) {
	return e[e.INITIATE_VERIFICATION = 0] = "INITIATE_VERIFICATION", e[e.PROVIDE_SECURITY_NUMBER = 1] = "PROVIDE_SECURITY_NUMBER", e[e.DO_VERIFY = 2] = "DO_VERIFY", e[e.DO_NOT_VERIFY = 3] = "DO_NOT_VERIFY", e;
}({}), Hl = /* @__PURE__ */ C(Ol, 4, 0), Ul = g({
	ChannelSetSchema: () => Gl,
	file_apponly: () => Wl
}), Wl = /* @__PURE__ */ T("Cg1hcHBvbmx5LnByb3RvEgptZXNodGFzdGljIm8KCkNoYW5uZWxTZXQSLQoIc2V0dGluZ3MYASADKAsyGy5tZXNodGFzdGljLkNoYW5uZWxTZXR0aW5ncxIyCgtsb3JhX2NvbmZpZxgCIAEoCzIdLm1lc2h0YXN0aWMuQ29uZmlnLkxvUmFDb25maWdCYgoTY29tLmdlZWtzdmlsbGUubWVzaEINQXBwT25seVByb3Rvc1oiZ2l0aHViLmNvbS9tZXNodGFzdGljL2dvL2dlbmVyYXRlZKoCFE1lc2h0YXN0aWMuUHJvdG9idWZzugIAYgZwcm90bzM", [Po, O]), Gl = /* @__PURE__ */ E(Wl, 0), Kl = g({
	ContactSchema: () => Ql,
	GeoChatSchema: () => Yl,
	GroupSchema: () => Xl,
	MemberRole: () => nu,
	MemberRoleSchema: () => ru,
	PLISchema: () => $l,
	StatusSchema: () => Zl,
	TAKPacketSchema: () => Jl,
	Team: () => eu,
	TeamSchema: () => tu,
	file_atak: () => ql
}), ql = /* @__PURE__ */ T("CgphdGFrLnByb3RvEgptZXNodGFzdGljIvgBCglUQUtQYWNrZXQSFQoNaXNfY29tcHJlc3NlZBgBIAEoCBIkCgdjb250YWN0GAIgASgLMhMubWVzaHRhc3RpYy5Db250YWN0EiAKBWdyb3VwGAMgASgLMhEubWVzaHRhc3RpYy5Hcm91cBIiCgZzdGF0dXMYBCABKAsyEi5tZXNodGFzdGljLlN0YXR1cxIeCgNwbGkYBSABKAsyDy5tZXNodGFzdGljLlBMSUgAEiMKBGNoYXQYBiABKAsyEy5tZXNodGFzdGljLkdlb0NoYXRIABIQCgZkZXRhaWwYByABKAxIAEIRCg9wYXlsb2FkX3ZhcmlhbnQiXAoHR2VvQ2hhdBIPCgdtZXNzYWdlGAEgASgJEg8KAnRvGAIgASgJSACIAQESGAoLdG9fY2FsbHNpZ24YAyABKAlIAYgBAUIFCgNfdG9CDgoMX3RvX2NhbGxzaWduIk0KBUdyb3VwEiQKBHJvbGUYASABKA4yFi5tZXNodGFzdGljLk1lbWJlclJvbGUSHgoEdGVhbRgCIAEoDjIQLm1lc2h0YXN0aWMuVGVhbSIZCgZTdGF0dXMSDwoHYmF0dGVyeRgBIAEoDSI0CgdDb250YWN0EhAKCGNhbGxzaWduGAEgASgJEhcKD2RldmljZV9jYWxsc2lnbhgCIAEoCSJfCgNQTEkSEgoKbGF0aXR1ZGVfaRgBIAEoDxITCgtsb25naXR1ZGVfaRgCIAEoDxIQCghhbHRpdHVkZRgDIAEoBRINCgVzcGVlZBgEIAEoDRIOCgZjb3Vyc2UYBSABKA0qwAEKBFRlYW0SFAoQVW5zcGVjaWZlZF9Db2xvchAAEgkKBVdoaXRlEAESCgoGWWVsbG93EAISCgoGT3JhbmdlEAMSCwoHTWFnZW50YRAEEgcKA1JlZBAFEgoKBk1hcm9vbhAGEgoKBlB1cnBsZRAHEg0KCURhcmtfQmx1ZRAIEggKBEJsdWUQCRIICgRDeWFuEAoSCAoEVGVhbBALEgkKBUdyZWVuEAwSDgoKRGFya19HcmVlbhANEgkKBUJyb3duEA4qfwoKTWVtYmVyUm9sZRIOCgpVbnNwZWNpZmVkEAASDgoKVGVhbU1lbWJlchABEgwKCFRlYW1MZWFkEAISBgoCSFEQAxIKCgZTbmlwZXIQBBIJCgVNZWRpYxAFEhMKD0ZvcndhcmRPYnNlcnZlchAGEgcKA1JUTxAHEgYKAks5EAhCXwoTY29tLmdlZWtzdmlsbGUubWVzaEIKQVRBS1Byb3Rvc1oiZ2l0aHViLmNvbS9tZXNodGFzdGljL2dvL2dlbmVyYXRlZKoCFE1lc2h0YXN0aWMuUHJvdG9idWZzugIAYgZwcm90bzM"), Jl = /* @__PURE__ */ E(ql, 0), Yl = /* @__PURE__ */ E(ql, 1), Xl = /* @__PURE__ */ E(ql, 2), Zl = /* @__PURE__ */ E(ql, 3), Ql = /* @__PURE__ */ E(ql, 4), $l = /* @__PURE__ */ E(ql, 5), eu = /* @__PURE__ */ function(e) {
	return e[e.Unspecifed_Color = 0] = "Unspecifed_Color", e[e.White = 1] = "White", e[e.Yellow = 2] = "Yellow", e[e.Orange = 3] = "Orange", e[e.Magenta = 4] = "Magenta", e[e.Red = 5] = "Red", e[e.Maroon = 6] = "Maroon", e[e.Purple = 7] = "Purple", e[e.Dark_Blue = 8] = "Dark_Blue", e[e.Blue = 9] = "Blue", e[e.Cyan = 10] = "Cyan", e[e.Teal = 11] = "Teal", e[e.Green = 12] = "Green", e[e.Dark_Green = 13] = "Dark_Green", e[e.Brown = 14] = "Brown", e;
}({}), tu = /* @__PURE__ */ C(ql, 0), nu = /* @__PURE__ */ function(e) {
	return e[e.Unspecifed = 0] = "Unspecifed", e[e.TeamMember = 1] = "TeamMember", e[e.TeamLead = 2] = "TeamLead", e[e.HQ = 3] = "HQ", e[e.Sniper = 4] = "Sniper", e[e.Medic = 5] = "Medic", e[e.ForwardObserver = 6] = "ForwardObserver", e[e.RTO = 7] = "RTO", e[e.K9 = 8] = "K9", e;
}({}), ru = /* @__PURE__ */ C(ql, 1), iu = g({
	CannedMessageModuleConfigSchema: () => ou,
	file_cannedmessages: () => au
}), au = /* @__PURE__ */ T("ChRjYW5uZWRtZXNzYWdlcy5wcm90bxIKbWVzaHRhc3RpYyItChlDYW5uZWRNZXNzYWdlTW9kdWxlQ29uZmlnEhAKCG1lc3NhZ2VzGAEgASgJQm4KE2NvbS5nZWVrc3ZpbGxlLm1lc2hCGUNhbm5lZE1lc3NhZ2VDb25maWdQcm90b3NaImdpdGh1Yi5jb20vbWVzaHRhc3RpYy9nby9nZW5lcmF0ZWSqAhRNZXNodGFzdGljLlByb3RvYnVmc7oCAGIGcHJvdG8z"), ou = /* @__PURE__ */ E(au, 0), su = g({
	LocalConfigSchema: () => lu,
	LocalModuleConfigSchema: () => uu,
	file_localonly: () => cu
}), cu = /* @__PURE__ */ T("Cg9sb2NhbG9ubHkucHJvdG8SCm1lc2h0YXN0aWMisgMKC0xvY2FsQ29uZmlnEi8KBmRldmljZRgBIAEoCzIfLm1lc2h0YXN0aWMuQ29uZmlnLkRldmljZUNvbmZpZxIzCghwb3NpdGlvbhgCIAEoCzIhLm1lc2h0YXN0aWMuQ29uZmlnLlBvc2l0aW9uQ29uZmlnEi0KBXBvd2VyGAMgASgLMh4ubWVzaHRhc3RpYy5Db25maWcuUG93ZXJDb25maWcSMQoHbmV0d29yaxgEIAEoCzIgLm1lc2h0YXN0aWMuQ29uZmlnLk5ldHdvcmtDb25maWcSMQoHZGlzcGxheRgFIAEoCzIgLm1lc2h0YXN0aWMuQ29uZmlnLkRpc3BsYXlDb25maWcSKwoEbG9yYRgGIAEoCzIdLm1lc2h0YXN0aWMuQ29uZmlnLkxvUmFDb25maWcSNQoJYmx1ZXRvb3RoGAcgASgLMiIubWVzaHRhc3RpYy5Db25maWcuQmx1ZXRvb3RoQ29uZmlnEg8KB3ZlcnNpb24YCCABKA0SMwoIc2VjdXJpdHkYCSABKAsyIS5tZXNodGFzdGljLkNvbmZpZy5TZWN1cml0eUNvbmZpZyL7BgoRTG9jYWxNb2R1bGVDb25maWcSMQoEbXF0dBgBIAEoCzIjLm1lc2h0YXN0aWMuTW9kdWxlQ29uZmlnLk1RVFRDb25maWcSNQoGc2VyaWFsGAIgASgLMiUubWVzaHRhc3RpYy5Nb2R1bGVDb25maWcuU2VyaWFsQ29uZmlnElIKFWV4dGVybmFsX25vdGlmaWNhdGlvbhgDIAEoCzIzLm1lc2h0YXN0aWMuTW9kdWxlQ29uZmlnLkV4dGVybmFsTm90aWZpY2F0aW9uQ29uZmlnEkIKDXN0b3JlX2ZvcndhcmQYBCABKAsyKy5tZXNodGFzdGljLk1vZHVsZUNvbmZpZy5TdG9yZUZvcndhcmRDb25maWcSPAoKcmFuZ2VfdGVzdBgFIAEoCzIoLm1lc2h0YXN0aWMuTW9kdWxlQ29uZmlnLlJhbmdlVGVzdENvbmZpZxI7Cgl0ZWxlbWV0cnkYBiABKAsyKC5tZXNodGFzdGljLk1vZHVsZUNvbmZpZy5UZWxlbWV0cnlDb25maWcSRAoOY2FubmVkX21lc3NhZ2UYByABKAsyLC5tZXNodGFzdGljLk1vZHVsZUNvbmZpZy5DYW5uZWRNZXNzYWdlQ29uZmlnEjMKBWF1ZGlvGAkgASgLMiQubWVzaHRhc3RpYy5Nb2R1bGVDb25maWcuQXVkaW9Db25maWcSRgoPcmVtb3RlX2hhcmR3YXJlGAogASgLMi0ubWVzaHRhc3RpYy5Nb2R1bGVDb25maWcuUmVtb3RlSGFyZHdhcmVDb25maWcSQgoNbmVpZ2hib3JfaW5mbxgLIAEoCzIrLm1lc2h0YXN0aWMuTW9kdWxlQ29uZmlnLk5laWdoYm9ySW5mb0NvbmZpZxJIChBhbWJpZW50X2xpZ2h0aW5nGAwgASgLMi4ubWVzaHRhc3RpYy5Nb2R1bGVDb25maWcuQW1iaWVudExpZ2h0aW5nQ29uZmlnEkgKEGRldGVjdGlvbl9zZW5zb3IYDSABKAsyLi5tZXNodGFzdGljLk1vZHVsZUNvbmZpZy5EZXRlY3Rpb25TZW5zb3JDb25maWcSPQoKcGF4Y291bnRlchgOIAEoCzIpLm1lc2h0YXN0aWMuTW9kdWxlQ29uZmlnLlBheGNvdW50ZXJDb25maWcSDwoHdmVyc2lvbhgIIAEoDUJkChNjb20uZ2Vla3N2aWxsZS5tZXNoQg9Mb2NhbE9ubHlQcm90b3NaImdpdGh1Yi5jb20vbWVzaHRhc3RpYy9nby9nZW5lcmF0ZWSqAhRNZXNodGFzdGljLlByb3RvYnVmc7oCAGIGcHJvdG8z", [O, k]), lu = /* @__PURE__ */ E(cu, 0), uu = /* @__PURE__ */ E(cu, 1), du = g({
	DeviceProfileSchema: () => pu,
	file_clientonly: () => fu
}), fu = /* @__PURE__ */ T("ChBjbGllbnRvbmx5LnByb3RvEgptZXNodGFzdGljIqkDCg1EZXZpY2VQcm9maWxlEhYKCWxvbmdfbmFtZRgBIAEoCUgAiAEBEhcKCnNob3J0X25hbWUYAiABKAlIAYgBARIYCgtjaGFubmVsX3VybBgDIAEoCUgCiAEBEiwKBmNvbmZpZxgEIAEoCzIXLm1lc2h0YXN0aWMuTG9jYWxDb25maWdIA4gBARI5Cg1tb2R1bGVfY29uZmlnGAUgASgLMh0ubWVzaHRhc3RpYy5Mb2NhbE1vZHVsZUNvbmZpZ0gEiAEBEjEKDmZpeGVkX3Bvc2l0aW9uGAYgASgLMhQubWVzaHRhc3RpYy5Qb3NpdGlvbkgFiAEBEhUKCHJpbmd0b25lGAcgASgJSAaIAQESHAoPY2FubmVkX21lc3NhZ2VzGAggASgJSAeIAQFCDAoKX2xvbmdfbmFtZUINCgtfc2hvcnRfbmFtZUIOCgxfY2hhbm5lbF91cmxCCQoHX2NvbmZpZ0IQCg5fbW9kdWxlX2NvbmZpZ0IRCg9fZml4ZWRfcG9zaXRpb25CCwoJX3Jpbmd0b25lQhIKEF9jYW5uZWRfbWVzc2FnZXNCZQoTY29tLmdlZWtzdmlsbGUubWVzaEIQQ2xpZW50T25seVByb3Rvc1oiZ2l0aHViLmNvbS9tZXNodGFzdGljL2dvL2dlbmVyYXRlZKoCFE1lc2h0YXN0aWMuUHJvdG9idWZzugIAYgZwcm90bzM", [cu, P]), pu = /* @__PURE__ */ E(fu, 0), mu = g({
	MapReportSchema: () => _u,
	ServiceEnvelopeSchema: () => gu,
	file_mqtt: () => hu
}), hu = /* @__PURE__ */ T("CgptcXR0LnByb3RvEgptZXNodGFzdGljImEKD1NlcnZpY2VFbnZlbG9wZRImCgZwYWNrZXQYASABKAsyFi5tZXNodGFzdGljLk1lc2hQYWNrZXQSEgoKY2hhbm5lbF9pZBgCIAEoCRISCgpnYXRld2F5X2lkGAMgASgJIt8DCglNYXBSZXBvcnQSEQoJbG9uZ19uYW1lGAEgASgJEhIKCnNob3J0X25hbWUYAiABKAkSMgoEcm9sZRgDIAEoDjIkLm1lc2h0YXN0aWMuQ29uZmlnLkRldmljZUNvbmZpZy5Sb2xlEisKCGh3X21vZGVsGAQgASgOMhkubWVzaHRhc3RpYy5IYXJkd2FyZU1vZGVsEhgKEGZpcm13YXJlX3ZlcnNpb24YBSABKAkSOAoGcmVnaW9uGAYgASgOMigubWVzaHRhc3RpYy5Db25maWcuTG9SYUNvbmZpZy5SZWdpb25Db2RlEj8KDG1vZGVtX3ByZXNldBgHIAEoDjIpLm1lc2h0YXN0aWMuQ29uZmlnLkxvUmFDb25maWcuTW9kZW1QcmVzZXQSGwoTaGFzX2RlZmF1bHRfY2hhbm5lbBgIIAEoCBISCgpsYXRpdHVkZV9pGAkgASgPEhMKC2xvbmdpdHVkZV9pGAogASgPEhAKCGFsdGl0dWRlGAsgASgFEhoKEnBvc2l0aW9uX3ByZWNpc2lvbhgMIAEoDRIeChZudW1fb25saW5lX2xvY2FsX25vZGVzGA0gASgNEiEKGWhhc19vcHRlZF9yZXBvcnRfbG9jYXRpb24YDiABKAhCXwoTY29tLmdlZWtzdmlsbGUubWVzaEIKTVFUVFByb3Rvc1oiZ2l0aHViLmNvbS9tZXNodGFzdGljL2dvL2dlbmVyYXRlZKoCFE1lc2h0YXN0aWMuUHJvdG9idWZzugIAYgZwcm90bzM", [O, P]), gu = /* @__PURE__ */ E(hu, 0), _u = /* @__PURE__ */ E(hu, 1), vu = g({
	PaxcountSchema: () => bu,
	file_paxcount: () => yu
}), yu = /* @__PURE__ */ T("Cg5wYXhjb3VudC5wcm90bxIKbWVzaHRhc3RpYyI1CghQYXhjb3VudBIMCgR3aWZpGAEgASgNEgsKA2JsZRgCIAEoDRIOCgZ1cHRpbWUYAyABKA1CYwoTY29tLmdlZWtzdmlsbGUubWVzaEIOUGF4Y291bnRQcm90b3NaImdpdGh1Yi5jb20vbWVzaHRhc3RpYy9nby9nZW5lcmF0ZWSqAhRNZXNodGFzdGljLlByb3RvYnVmc7oCAGIGcHJvdG8z"), bu = /* @__PURE__ */ E(yu, 0), xu = g({
	PowerMonSchema: () => Cu,
	PowerMon_State: () => wu,
	PowerMon_StateSchema: () => Tu,
	PowerStressMessageSchema: () => Eu,
	PowerStressMessage_Opcode: () => Du,
	PowerStressMessage_OpcodeSchema: () => Ou,
	file_powermon: () => Su
}), Su = /* @__PURE__ */ T("Cg5wb3dlcm1vbi5wcm90bxIKbWVzaHRhc3RpYyLgAQoIUG93ZXJNb24i0wEKBVN0YXRlEggKBE5vbmUQABIRCg1DUFVfRGVlcFNsZWVwEAESEgoOQ1BVX0xpZ2h0U2xlZXAQAhIMCghWZXh0MV9PbhAEEg0KCUxvcmFfUlhPbhAIEg0KCUxvcmFfVFhPbhAQEhEKDUxvcmFfUlhBY3RpdmUQIBIJCgVCVF9PbhBAEgsKBkxFRF9PbhCAARIOCglTY3JlZW5fT24QgAISEwoOU2NyZWVuX0RyYXdpbmcQgAQSDAoHV2lmaV9PbhCACBIPCgpHUFNfQWN0aXZlEIAQIv8CChJQb3dlclN0cmVzc01lc3NhZ2USMgoDY21kGAEgASgOMiUubWVzaHRhc3RpYy5Qb3dlclN0cmVzc01lc3NhZ2UuT3Bjb2RlEhMKC251bV9zZWNvbmRzGAIgASgCIp8CCgZPcGNvZGUSCQoFVU5TRVQQABIOCgpQUklOVF9JTkZPEAESDwoLRk9SQ0VfUVVJRVQQAhINCglFTkRfUVVJRVQQAxINCglTQ1JFRU5fT04QEBIOCgpTQ1JFRU5fT0ZGEBESDAoIQ1BVX0lETEUQIBIRCg1DUFVfREVFUFNMRUVQECESDgoKQ1BVX0ZVTExPThAiEgoKBkxFRF9PThAwEgsKB0xFRF9PRkYQMRIMCghMT1JBX09GRhBAEgsKB0xPUkFfVFgQQRILCgdMT1JBX1JYEEISCgoGQlRfT0ZGEFASCQoFQlRfT04QURIMCghXSUZJX09GRhBgEgsKB1dJRklfT04QYRILCgdHUFNfT0ZGEHASCgoGR1BTX09OEHFCYwoTY29tLmdlZWtzdmlsbGUubWVzaEIOUG93ZXJNb25Qcm90b3NaImdpdGh1Yi5jb20vbWVzaHRhc3RpYy9nby9nZW5lcmF0ZWSqAhRNZXNodGFzdGljLlByb3RvYnVmc7oCAGIGcHJvdG8z"), Cu = /* @__PURE__ */ E(Su, 0), wu = /* @__PURE__ */ function(e) {
	return e[e.None = 0] = "None", e[e.CPU_DeepSleep = 1] = "CPU_DeepSleep", e[e.CPU_LightSleep = 2] = "CPU_LightSleep", e[e.Vext1_On = 4] = "Vext1_On", e[e.Lora_RXOn = 8] = "Lora_RXOn", e[e.Lora_TXOn = 16] = "Lora_TXOn", e[e.Lora_RXActive = 32] = "Lora_RXActive", e[e.BT_On = 64] = "BT_On", e[e.LED_On = 128] = "LED_On", e[e.Screen_On = 256] = "Screen_On", e[e.Screen_Drawing = 512] = "Screen_Drawing", e[e.Wifi_On = 1024] = "Wifi_On", e[e.GPS_Active = 2048] = "GPS_Active", e;
}({}), Tu = /* @__PURE__ */ C(Su, 0, 0), Eu = /* @__PURE__ */ E(Su, 1), Du = /* @__PURE__ */ function(e) {
	return e[e.UNSET = 0] = "UNSET", e[e.PRINT_INFO = 1] = "PRINT_INFO", e[e.FORCE_QUIET = 2] = "FORCE_QUIET", e[e.END_QUIET = 3] = "END_QUIET", e[e.SCREEN_ON = 16] = "SCREEN_ON", e[e.SCREEN_OFF = 17] = "SCREEN_OFF", e[e.CPU_IDLE = 32] = "CPU_IDLE", e[e.CPU_DEEPSLEEP = 33] = "CPU_DEEPSLEEP", e[e.CPU_FULLON = 34] = "CPU_FULLON", e[e.LED_ON = 48] = "LED_ON", e[e.LED_OFF = 49] = "LED_OFF", e[e.LORA_OFF = 64] = "LORA_OFF", e[e.LORA_TX = 65] = "LORA_TX", e[e.LORA_RX = 66] = "LORA_RX", e[e.BT_OFF = 80] = "BT_OFF", e[e.BT_ON = 81] = "BT_ON", e[e.WIFI_OFF = 96] = "WIFI_OFF", e[e.WIFI_ON = 97] = "WIFI_ON", e[e.GPS_OFF = 112] = "GPS_OFF", e[e.GPS_ON = 113] = "GPS_ON", e;
}({}), Ou = /* @__PURE__ */ C(Su, 1, 0), ku = g({
	HardwareMessageSchema: () => ju,
	HardwareMessage_Type: () => Mu,
	HardwareMessage_TypeSchema: () => Nu,
	file_remote_hardware: () => Au
}), Au = /* @__PURE__ */ T("ChVyZW1vdGVfaGFyZHdhcmUucHJvdG8SCm1lc2h0YXN0aWMi1gEKD0hhcmR3YXJlTWVzc2FnZRIuCgR0eXBlGAEgASgOMiAubWVzaHRhc3RpYy5IYXJkd2FyZU1lc3NhZ2UuVHlwZRIRCglncGlvX21hc2sYAiABKAQSEgoKZ3Bpb192YWx1ZRgDIAEoBCJsCgRUeXBlEgkKBVVOU0VUEAASDwoLV1JJVEVfR1BJT1MQARIPCgtXQVRDSF9HUElPUxACEhEKDUdQSU9TX0NIQU5HRUQQAxIOCgpSRUFEX0dQSU9TEAQSFAoQUkVBRF9HUElPU19SRVBMWRAFQmMKE2NvbS5nZWVrc3ZpbGxlLm1lc2hCDlJlbW90ZUhhcmR3YXJlWiJnaXRodWIuY29tL21lc2h0YXN0aWMvZ28vZ2VuZXJhdGVkqgIUTWVzaHRhc3RpYy5Qcm90b2J1ZnO6AgBiBnByb3RvMw"), ju = /* @__PURE__ */ E(Au, 0), Mu = /* @__PURE__ */ function(e) {
	return e[e.UNSET = 0] = "UNSET", e[e.WRITE_GPIOS = 1] = "WRITE_GPIOS", e[e.WATCH_GPIOS = 2] = "WATCH_GPIOS", e[e.GPIOS_CHANGED = 3] = "GPIOS_CHANGED", e[e.READ_GPIOS = 4] = "READ_GPIOS", e[e.READ_GPIOS_REPLY = 5] = "READ_GPIOS_REPLY", e;
}({}), Nu = /* @__PURE__ */ C(Au, 0, 0), Pu = g({
	RTTTLConfigSchema: () => Iu,
	file_rtttl: () => Fu
}), Fu = /* @__PURE__ */ T("CgtydHR0bC5wcm90bxIKbWVzaHRhc3RpYyIfCgtSVFRUTENvbmZpZxIQCghyaW5ndG9uZRgBIAEoCUJmChNjb20uZ2Vla3N2aWxsZS5tZXNoQhFSVFRUTENvbmZpZ1Byb3Rvc1oiZ2l0aHViLmNvbS9tZXNodGFzdGljL2dvL2dlbmVyYXRlZKoCFE1lc2h0YXN0aWMuUHJvdG9idWZzugIAYgZwcm90bzM"), Iu = /* @__PURE__ */ E(Fu, 0), Lu = g({
	StoreAndForwardSchema: () => zu,
	StoreAndForward_HeartbeatSchema: () => Hu,
	StoreAndForward_HistorySchema: () => Vu,
	StoreAndForward_RequestResponse: () => Uu,
	StoreAndForward_RequestResponseSchema: () => Wu,
	StoreAndForward_StatisticsSchema: () => Bu,
	file_storeforward: () => Ru
}), Ru = /* @__PURE__ */ T("ChJzdG9yZWZvcndhcmQucHJvdG8SCm1lc2h0YXN0aWMinAcKD1N0b3JlQW5kRm9yd2FyZBI3CgJychgBIAEoDjIrLm1lc2h0YXN0aWMuU3RvcmVBbmRGb3J3YXJkLlJlcXVlc3RSZXNwb25zZRI3CgVzdGF0cxgCIAEoCzImLm1lc2h0YXN0aWMuU3RvcmVBbmRGb3J3YXJkLlN0YXRpc3RpY3NIABI2CgdoaXN0b3J5GAMgASgLMiMubWVzaHRhc3RpYy5TdG9yZUFuZEZvcndhcmQuSGlzdG9yeUgAEjoKCWhlYXJ0YmVhdBgEIAEoCzIlLm1lc2h0YXN0aWMuU3RvcmVBbmRGb3J3YXJkLkhlYXJ0YmVhdEgAEg4KBHRleHQYBSABKAxIABrNAQoKU3RhdGlzdGljcxIWCg5tZXNzYWdlc190b3RhbBgBIAEoDRIWCg5tZXNzYWdlc19zYXZlZBgCIAEoDRIUCgxtZXNzYWdlc19tYXgYAyABKA0SDwoHdXBfdGltZRgEIAEoDRIQCghyZXF1ZXN0cxgFIAEoDRIYChByZXF1ZXN0c19oaXN0b3J5GAYgASgNEhEKCWhlYXJ0YmVhdBgHIAEoCBISCgpyZXR1cm5fbWF4GAggASgNEhUKDXJldHVybl93aW5kb3cYCSABKA0aSQoHSGlzdG9yeRIYChBoaXN0b3J5X21lc3NhZ2VzGAEgASgNEg4KBndpbmRvdxgCIAEoDRIUCgxsYXN0X3JlcXVlc3QYAyABKA0aLgoJSGVhcnRiZWF0Eg4KBnBlcmlvZBgBIAEoDRIRCglzZWNvbmRhcnkYAiABKA0ivAIKD1JlcXVlc3RSZXNwb25zZRIJCgVVTlNFVBAAEhAKDFJPVVRFUl9FUlJPUhABEhQKEFJPVVRFUl9IRUFSVEJFQVQQAhIPCgtST1VURVJfUElORxADEg8KC1JPVVRFUl9QT05HEAQSDwoLUk9VVEVSX0JVU1kQBRISCg5ST1VURVJfSElTVE9SWRAGEhAKDFJPVVRFUl9TVEFUUxAHEhYKElJPVVRFUl9URVhUX0RJUkVDVBAIEhkKFVJPVVRFUl9URVhUX0JST0FEQ0FTVBAJEhAKDENMSUVOVF9FUlJPUhBAEhIKDkNMSUVOVF9ISVNUT1JZEEESEAoMQ0xJRU5UX1NUQVRTEEISDwoLQ0xJRU5UX1BJTkcQQxIPCgtDTElFTlRfUE9ORxBEEhAKDENMSUVOVF9BQk9SVBBqQgkKB3ZhcmlhbnRCagoTY29tLmdlZWtzdmlsbGUubWVzaEIVU3RvcmVBbmRGb3J3YXJkUHJvdG9zWiJnaXRodWIuY29tL21lc2h0YXN0aWMvZ28vZ2VuZXJhdGVkqgIUTWVzaHRhc3RpYy5Qcm90b2J1ZnO6AgBiBnByb3RvMw"), zu = /* @__PURE__ */ E(Ru, 0), Bu = /* @__PURE__ */ E(Ru, 0, 0), Vu = /* @__PURE__ */ E(Ru, 0, 1), Hu = /* @__PURE__ */ E(Ru, 0, 2), Uu = /* @__PURE__ */ function(e) {
	return e[e.UNSET = 0] = "UNSET", e[e.ROUTER_ERROR = 1] = "ROUTER_ERROR", e[e.ROUTER_HEARTBEAT = 2] = "ROUTER_HEARTBEAT", e[e.ROUTER_PING = 3] = "ROUTER_PING", e[e.ROUTER_PONG = 4] = "ROUTER_PONG", e[e.ROUTER_BUSY = 5] = "ROUTER_BUSY", e[e.ROUTER_HISTORY = 6] = "ROUTER_HISTORY", e[e.ROUTER_STATS = 7] = "ROUTER_STATS", e[e.ROUTER_TEXT_DIRECT = 8] = "ROUTER_TEXT_DIRECT", e[e.ROUTER_TEXT_BROADCAST = 9] = "ROUTER_TEXT_BROADCAST", e[e.CLIENT_ERROR = 64] = "CLIENT_ERROR", e[e.CLIENT_HISTORY = 65] = "CLIENT_HISTORY", e[e.CLIENT_STATS = 66] = "CLIENT_STATS", e[e.CLIENT_PING = 67] = "CLIENT_PING", e[e.CLIENT_PONG = 68] = "CLIENT_PONG", e[e.CLIENT_ABORT = 106] = "CLIENT_ABORT", e;
}({}), Wu = /* @__PURE__ */ C(Ru, 0, 0), Gu = {
	broadcastNum: 4294967295,
	minFwVer: 2.2
}, Ku = {
	reset: [0, 0],
	bold: [1, 22],
	dim: [2, 22],
	italic: [3, 23],
	underline: [4, 24],
	overline: [53, 55],
	inverse: [7, 27],
	hidden: [8, 28],
	strikethrough: [9, 29],
	black: [30, 39],
	red: [31, 39],
	green: [32, 39],
	yellow: [33, 39],
	blue: [34, 39],
	magenta: [35, 39],
	cyan: [36, 39],
	white: [37, 39],
	blackBright: [90, 39],
	redBright: [91, 39],
	greenBright: [92, 39],
	yellowBright: [93, 39],
	blueBright: [94, 39],
	magentaBright: [95, 39],
	cyanBright: [96, 39],
	whiteBright: [97, 39],
	bgBlack: [40, 49],
	bgRed: [41, 49],
	bgGreen: [42, 49],
	bgYellow: [43, 49],
	bgBlue: [44, 49],
	bgMagenta: [45, 49],
	bgCyan: [46, 49],
	bgWhite: [47, 49],
	bgBlackBright: [100, 49],
	bgRedBright: [101, 49],
	bgGreenBright: [102, 49],
	bgYellowBright: [103, 49],
	bgBlueBright: [104, 49],
	bgMagentaBright: [105, 49],
	bgCyanBright: [106, 49],
	bgWhiteBright: [107, 49]
};
function qu(e, t, n, r = !1) {
	let i = String(t), a = (e, t) => `\u001b[${t[0]}m${e}\u001b[${t[1]}m`, o = (e, t) => t != null && typeof t == "string" ? a(e, Ku[t]) : t != null && Array.isArray(t) ? t.reduce((e, t) => o(e, t), e) : t != null && t[e.trim()] != null ? o(e, t[e.trim()]) : t != null && t["*"] != null ? o(e, t["*"]) : e;
	return i.replace(/{{(.+?)}}/g, (t, i) => {
		let s = n[i] == null ? r ? "" : t : String(n[i]);
		return e.stylePrettyLogs ? o(s, e?.prettyLogStyles?.[i] ?? null) + a("", Ku.reset) : s;
	});
}
function I(e, t = 2, n = 0) {
	return e != null && isNaN(e) ? "" : (e = e == null ? e : e + n, t === 2 ? e == null ? "--" : e < 10 ? "0" + e : e.toString() : e == null ? "---" : e < 10 ? "00" + e : e < 100 ? "0" + e : e.toString());
}
function Ju(e) {
	return {
		href: e.href,
		protocol: e.protocol,
		username: e.username,
		password: e.password,
		host: e.host,
		hostname: e.hostname,
		port: e.port,
		pathname: e.pathname,
		search: e.search,
		searchParams: [...e.searchParams].map(([e, t]) => ({
			key: e,
			value: t
		})),
		hash: e.hash,
		origin: e.origin
	};
}
var Yu = {
	getCallerStackFrame: Qu,
	getErrorTrace: $u,
	getMeta: Zu,
	transportJSON: ad,
	transportFormatted: id,
	isBuffer: od,
	isError: td,
	prettyFormatLogObj: nd,
	prettyFormatErrorObj: rd
}, Xu = {
	runtime: "Nodejs",
	runtimeVersion: "",
	hostname: qn ? qn() : void 0
};
function Zu(e, t, n, r, i, a) {
	return Object.assign({}, Xu, {
		name: i,
		parentNames: a,
		date: /* @__PURE__ */ new Date(),
		logLevelId: e,
		logLevelName: t,
		path: r ? void 0 : Qu(n)
	});
}
function Qu(e, t = Error()) {
	return ed(t?.stack?.split("\n")?.filter((e) => e.includes("    at "))?.[e]);
}
function $u(e) {
	return e?.stack?.split("\n")?.reduce((e, t) => (t.includes("    at ") && e.push(ed(t)), e), []);
}
function ed(e) {
	let t = {
		fullFilePath: void 0,
		fileName: void 0,
		fileNameWithLine: void 0,
		fileColumn: void 0,
		fileLine: void 0,
		filePath: void 0,
		filePathWithLine: void 0,
		method: void 0
	};
	if (e != null && e.includes("    at ")) {
		e = e.replace(/^\s+at\s+/gm, "");
		let n = e.split(" ("), r = e?.slice(-1) === ")" ? e?.match(/\(([^)]+)\)/)?.[1] : e, i = r?.includes(":") ? r?.replace("file://", "")?.replace("", "")?.split(":") : void 0, a = i?.pop(), o = i?.pop(), s = i?.pop(), c = Jn(`${s}:${o}`), l = s?.split("/")?.pop(), u = `${l}:${o}`;
		s != null && s.length > 0 && (t.fullFilePath = r, t.fileName = l, t.fileNameWithLine = u, t.fileColumn = a, t.fileLine = o, t.filePath = s, t.filePathWithLine = c, t.method = n?.[1] == null ? void 0 : n?.[0]);
	}
	return t;
}
function td(e) {
	return Xn?.isNativeError == null ? e instanceof Error : Xn.isNativeError(e);
}
function nd(e, t) {
	return e.reduce((e, n) => (td(n) ? e.errors.push(rd(n, t)) : e.args.push(n), e), {
		args: [],
		errors: []
	});
}
function rd(e, t) {
	let n = $u(e).map((e) => qu(t, t.prettyErrorStackTemplate, { ...e }, !0)), r = {
		errorName: ` ${e.name} `,
		errorMessage: Object.getOwnPropertyNames(e).reduce((t, n) => (n !== "stack" && t.push(e[n]), t), []).join(", "),
		errorStack: n.join("\n")
	};
	return qu(t, t.prettyErrorTemplate, r);
}
function id(e, t, n, r) {
	let i = (n.length > 0 && t.length > 0 ? "\n" : "") + n.join("\n");
	r.prettyInspectOptions.colors = r.stylePrettyLogs, console.log(e + Yn(r.prettyInspectOptions, ...t) + i);
}
function ad(e) {
	console.log(t(e));
	function t(e) {
		let t = /* @__PURE__ */ new Set();
		return JSON.stringify(e, (e, n) => {
			if (typeof n == "object" && n) {
				if (t.has(n)) return "[Circular]";
				t.add(n);
			}
			return typeof n == "bigint" ? `${n}` : n === void 0 ? "[undefined]" : n;
		});
	}
}
function od(e) {
	return ((e) => !!(e && (e._isBuffer || e.constructor && e.constructor.isBuffer && e.constructor.isBuffer(e))))(e);
}
var sd = class {
	constructor(e, t, n = 4) {
		this.logObj = t, this.stackDepthLevel = n, this.runtime = Yu, this.settings = {
			type: e?.type ?? "pretty",
			name: e?.name,
			parentNames: e?.parentNames,
			minLevel: e?.minLevel ?? 0,
			argumentsArrayName: e?.argumentsArrayName,
			hideLogPositionForProduction: e?.hideLogPositionForProduction ?? !1,
			prettyLogTemplate: e?.prettyLogTemplate ?? "{{yyyy}}.{{mm}}.{{dd}} {{hh}}:{{MM}}:{{ss}}:{{ms}}	{{logLevelName}}	{{filePathWithLine}}{{nameWithDelimiterPrefix}}	",
			prettyErrorTemplate: e?.prettyErrorTemplate ?? "\n{{errorName}} {{errorMessage}}\nerror stack:\n{{errorStack}}",
			prettyErrorStackTemplate: e?.prettyErrorStackTemplate ?? "  • {{fileName}}	{{method}}\n	{{filePathWithLine}}",
			prettyErrorParentNamesSeparator: e?.prettyErrorParentNamesSeparator ?? ":",
			prettyErrorLoggerNameDelimiter: e?.prettyErrorLoggerNameDelimiter ?? "	",
			stylePrettyLogs: e?.stylePrettyLogs ?? !0,
			prettyLogTimeZone: e?.prettyLogTimeZone ?? "UTC",
			prettyLogStyles: e?.prettyLogStyles ?? {
				logLevelName: {
					"*": [
						"bold",
						"black",
						"bgWhiteBright",
						"dim"
					],
					SILLY: ["bold", "white"],
					TRACE: ["bold", "whiteBright"],
					DEBUG: ["bold", "green"],
					INFO: ["bold", "blue"],
					WARN: ["bold", "yellow"],
					ERROR: ["bold", "red"],
					FATAL: ["bold", "redBright"]
				},
				dateIsoStr: "white",
				filePathWithLine: "white",
				name: ["white", "bold"],
				nameWithDelimiterPrefix: ["white", "bold"],
				nameWithDelimiterSuffix: ["white", "bold"],
				errorName: [
					"bold",
					"bgRedBright",
					"whiteBright"
				],
				fileName: ["yellow"],
				fileNameWithLine: "white"
			},
			prettyInspectOptions: e?.prettyInspectOptions ?? {
				colors: !0,
				compact: !1,
				depth: Infinity
			},
			metaProperty: e?.metaProperty ?? "_meta",
			maskPlaceholder: e?.maskPlaceholder ?? "[***]",
			maskValuesOfKeys: e?.maskValuesOfKeys ?? ["password"],
			maskValuesOfKeysCaseInsensitive: e?.maskValuesOfKeysCaseInsensitive ?? !1,
			maskValuesRegEx: e?.maskValuesRegEx,
			prefix: [...e?.prefix ?? []],
			attachedTransports: [...e?.attachedTransports ?? []],
			overwrite: {
				mask: e?.overwrite?.mask,
				toLogObj: e?.overwrite?.toLogObj,
				addMeta: e?.overwrite?.addMeta,
				addPlaceholders: e?.overwrite?.addPlaceholders,
				formatMeta: e?.overwrite?.formatMeta,
				formatLogObj: e?.overwrite?.formatLogObj,
				transportFormatted: e?.overwrite?.transportFormatted,
				transportJSON: e?.overwrite?.transportJSON
			}
		};
	}
	log(e, t, ...n) {
		if (e < this.settings.minLevel) return;
		let r = [...this.settings.prefix, ...n], i = this.settings.overwrite?.mask == null ? this.settings.maskValuesOfKeys != null && this.settings.maskValuesOfKeys.length > 0 ? this._mask(r) : r : this.settings.overwrite?.mask(r), a = this.logObj == null ? void 0 : this._recursiveCloneAndExecuteFunctions(this.logObj), o = this.settings.overwrite?.toLogObj == null ? this._toLogObj(i, a) : this.settings.overwrite?.toLogObj(i, a), s = this.settings.overwrite?.addMeta == null ? this._addMetaToLogObj(o, e, t) : this.settings.overwrite?.addMeta(o, e, t), c, l;
		return this.settings.overwrite?.formatMeta != null && (c = this.settings.overwrite?.formatMeta(s?.[this.settings.metaProperty])), this.settings.overwrite?.formatLogObj != null && (l = this.settings.overwrite?.formatLogObj(i, this.settings)), this.settings.type === "pretty" && (c ??= this._prettyFormatLogObjMeta(s?.[this.settings.metaProperty]), l ??= this.runtime.prettyFormatLogObj(i, this.settings)), c != null && l != null ? this.settings.overwrite?.transportFormatted == null ? this.runtime.transportFormatted(c, l.args, l.errors, this.settings) : this.settings.overwrite?.transportFormatted(c, l.args, l.errors, this.settings) : this.settings.overwrite?.transportJSON == null ? this.settings.type !== "hidden" && this.runtime.transportJSON(s) : this.settings.overwrite?.transportJSON(s), this.settings.attachedTransports != null && this.settings.attachedTransports.length > 0 && this.settings.attachedTransports.forEach((e) => {
			e(s);
		}), s;
	}
	attachTransport(e) {
		this.settings.attachedTransports.push(e);
	}
	getSubLogger(e, t) {
		let n = {
			...this.settings,
			...e,
			parentNames: this.settings?.parentNames != null && this.settings?.name != null ? [...this.settings.parentNames, this.settings.name] : this.settings?.name == null ? void 0 : [this.settings.name],
			prefix: [...this.settings.prefix, ...e?.prefix ?? []]
		};
		return new this.constructor(n, t ?? this.logObj, this.stackDepthLevel);
	}
	_mask(e) {
		let t = this.settings.maskValuesOfKeysCaseInsensitive === !0 ? this.settings.maskValuesOfKeys.map((e) => e.toLowerCase()) : this.settings.maskValuesOfKeys;
		return e?.map((e) => this._recursiveCloneAndMaskValuesOfKeys(e, t));
	}
	_recursiveCloneAndMaskValuesOfKeys(e, t, n = []) {
		if (n.includes(e)) return { ...e };
		if (typeof e == "object" && e && n.push(e), this.runtime.isError(e) || this.runtime.isBuffer(e)) return e;
		if (e instanceof Map) return new Map(e);
		if (e instanceof Set) return new Set(e);
		if (Array.isArray(e)) return e.map((e) => this._recursiveCloneAndMaskValuesOfKeys(e, t, n));
		if (e instanceof Date) return new Date(e.getTime());
		if (e instanceof URL) return Ju(e);
		if (typeof e == "object" && e) {
			let r = this.runtime.isError(e) ? this._cloneError(e) : Object.create(Object.getPrototypeOf(e));
			return Object.getOwnPropertyNames(e).reduce((r, i) => (r[i] = t.includes(this.settings?.maskValuesOfKeysCaseInsensitive === !0 ? i.toLowerCase() : i) ? this.settings.maskPlaceholder : (() => {
				try {
					return this._recursiveCloneAndMaskValuesOfKeys(e[i], t, n);
				} catch {
					return null;
				}
			})(), r), r);
		}
		if (typeof e == "string") {
			let t = e;
			for (let e of this.settings?.maskValuesRegEx || []) t = t.replace(e, this.settings?.maskPlaceholder || "");
			return t;
		}
		return e;
	}
	_recursiveCloneAndExecuteFunctions(e, t = []) {
		return this.isObjectOrArray(e) && t.includes(e) ? this.shallowCopy(e) : (this.isObjectOrArray(e) && t.push(e), Array.isArray(e) ? e.map((e) => this._recursiveCloneAndExecuteFunctions(e, t)) : e instanceof Date ? new Date(e.getTime()) : this.isObject(e) ? Object.getOwnPropertyNames(e).reduce((n, r) => {
			let i = Object.getOwnPropertyDescriptor(e, r);
			if (i) {
				Object.defineProperty(n, r, i);
				let a = e[r];
				n[r] = typeof a == "function" ? a() : this._recursiveCloneAndExecuteFunctions(a, t);
			}
			return n;
		}, Object.create(Object.getPrototypeOf(e))) : e);
	}
	isObjectOrArray(e) {
		return typeof e == "object" && !!e;
	}
	isObject(e) {
		return typeof e == "object" && !Array.isArray(e) && e !== null;
	}
	shallowCopy(e) {
		return Array.isArray(e) ? [...e] : { ...e };
	}
	_toLogObj(e, t = {}) {
		return e = e?.map((e) => this.runtime.isError(e) ? this._toErrorObject(e) : e), t = this.settings.argumentsArrayName == null ? e.length === 1 && !Array.isArray(e[0]) && this.runtime.isBuffer(e[0]) !== !0 && !(e[0] instanceof Date) ? typeof e[0] == "object" && e[0] != null ? {
			...e[0],
			...t
		} : {
			0: e[0],
			...t
		} : {
			...t,
			...e
		} : {
			...t,
			[this.settings.argumentsArrayName]: e
		}, t;
	}
	_cloneError(e) {
		let t = new e.constructor();
		return Object.getOwnPropertyNames(e).forEach((n) => {
			t[n] = e[n];
		}), t;
	}
	_toErrorObject(e) {
		return {
			nativeError: e,
			name: e.name ?? "Error",
			message: e.message,
			stack: this.runtime.getErrorTrace(e)
		};
	}
	_addMetaToLogObj(e, t, n) {
		return {
			...e,
			[this.settings.metaProperty]: this.runtime.getMeta(t, n, this.stackDepthLevel, this.settings.hideLogPositionForProduction, this.settings.name, this.settings.parentNames)
		};
	}
	_prettyFormatLogObjMeta(e) {
		if (e == null) return "";
		let t = this.settings.prettyLogTemplate, n = {};
		t.includes("{{yyyy}}.{{mm}}.{{dd}} {{hh}}:{{MM}}:{{ss}}:{{ms}}") ? t = t.replace("{{yyyy}}.{{mm}}.{{dd}} {{hh}}:{{MM}}:{{ss}}:{{ms}}", "{{dateIsoStr}}") : this.settings.prettyLogTimeZone === "UTC" ? (n.yyyy = e?.date?.getUTCFullYear() ?? "----", n.mm = I(e?.date?.getUTCMonth(), 2, 1), n.dd = I(e?.date?.getUTCDate(), 2), n.hh = I(e?.date?.getUTCHours(), 2), n.MM = I(e?.date?.getUTCMinutes(), 2), n.ss = I(e?.date?.getUTCSeconds(), 2), n.ms = I(e?.date?.getUTCMilliseconds(), 3)) : (n.yyyy = e?.date?.getFullYear() ?? "----", n.mm = I(e?.date?.getMonth(), 2, 1), n.dd = I(e?.date?.getDate(), 2), n.hh = I(e?.date?.getHours(), 2), n.MM = I(e?.date?.getMinutes(), 2), n.ss = I(e?.date?.getSeconds(), 2), n.ms = I(e?.date?.getMilliseconds(), 3));
		let r = this.settings.prettyLogTimeZone === "UTC" ? e?.date : /* @__PURE__ */ new Date(e?.date?.getTime() - e?.date?.getTimezoneOffset() * 6e4);
		n.rawIsoStr = r?.toISOString(), n.dateIsoStr = r?.toISOString().replace("T", " ").replace("Z", ""), n.logLevelName = e?.logLevelName, n.fileNameWithLine = e?.path?.fileNameWithLine ?? "", n.filePathWithLine = e?.path?.filePathWithLine ?? "", n.fullFilePath = e?.path?.fullFilePath ?? "";
		let i = this.settings.parentNames?.join(this.settings.prettyErrorParentNamesSeparator);
		return i = i != null && e?.name != null ? i + this.settings.prettyErrorParentNamesSeparator : void 0, n.name = e?.name != null || i != null ? (i ?? "") + e?.name ?? "" : "", n.nameWithDelimiterPrefix = n.name.length > 0 ? this.settings.prettyErrorLoggerNameDelimiter + n.name : "", n.nameWithDelimiterSuffix = n.name.length > 0 ? n.name + this.settings.prettyErrorLoggerNameDelimiter : "", this.settings.overwrite?.addPlaceholders != null && this.settings.overwrite?.addPlaceholders(e, n), qu(this.settings, t, n);
	}
}, cd = class extends sd {
	constructor(e, t) {
		let n = typeof window < "u" && typeof document < "u", r = n ? window.chrome !== void 0 && window.CSS !== void 0 && window.CSS.supports("color", "green") : !1, i = n ? /^((?!chrome|android).)*safari/i.test(navigator.userAgent) : !1;
		e ||= {}, e.stylePrettyLogs = e.stylePrettyLogs && n && !r ? !1 : e.stylePrettyLogs, super(e, t, i ? 4 : 5);
	}
	log(e, t, ...n) {
		return super.log(e, t, ...n);
	}
	silly(...e) {
		return super.log(0, "SILLY", ...e);
	}
	trace(...e) {
		return super.log(1, "TRACE", ...e);
	}
	debug(...e) {
		return super.log(2, "DEBUG", ...e);
	}
	info(...e) {
		return super.log(3, "INFO", ...e);
	}
	warn(...e) {
		return super.log(4, "WARN", ...e);
	}
	error(...e) {
		return super.log(5, "ERROR", ...e);
	}
	fatal(...e) {
		return super.log(6, "FATAL", ...e);
	}
	getSubLogger(e, t) {
		return super.getSubLogger(e, t);
	}
}, L = Wn({
	ChannelNumber: () => dd,
	DeviceStatusEnum: () => ld,
	Emitter: () => R,
	EmitterScope: () => ud
}), ld = /* @__PURE__ */ function(e) {
	return e[e.DeviceRestarting = 1] = "DeviceRestarting", e[e.DeviceDisconnected = 2] = "DeviceDisconnected", e[e.DeviceConnecting = 3] = "DeviceConnecting", e[e.DeviceReconnecting = 4] = "DeviceReconnecting", e[e.DeviceConnected = 5] = "DeviceConnected", e[e.DeviceConfiguring = 6] = "DeviceConfiguring", e[e.DeviceConfigured = 7] = "DeviceConfigured", e;
}({}), ud = /* @__PURE__ */ function(e) {
	return e[e.MeshDevice = 1] = "MeshDevice", e[e.SerialConnection = 2] = "SerialConnection", e[e.NodeSerialConnection = 3] = "NodeSerialConnection", e[e.BleConnection = 4] = "BleConnection", e[e.HttpConnection = 5] = "HttpConnection", e;
}({}), R = /* @__PURE__ */ function(e) {
	return e[e.Constructor = 0] = "Constructor", e[e.SendText = 1] = "SendText", e[e.SendWaypoint = 2] = "SendWaypoint", e[e.SendPacket = 3] = "SendPacket", e[e.SendRaw = 4] = "SendRaw", e[e.SetConfig = 5] = "SetConfig", e[e.SetModuleConfig = 6] = "SetModuleConfig", e[e.ConfirmSetConfig = 7] = "ConfirmSetConfig", e[e.SetOwner = 8] = "SetOwner", e[e.SetChannel = 9] = "SetChannel", e[e.ConfirmSetChannel = 10] = "ConfirmSetChannel", e[e.ClearChannel = 11] = "ClearChannel", e[e.GetChannel = 12] = "GetChannel", e[e.GetAllChannels = 13] = "GetAllChannels", e[e.GetConfig = 14] = "GetConfig", e[e.GetModuleConfig = 15] = "GetModuleConfig", e[e.GetOwner = 16] = "GetOwner", e[e.Configure = 17] = "Configure", e[e.HandleFromRadio = 18] = "HandleFromRadio", e[e.HandleMeshPacket = 19] = "HandleMeshPacket", e[e.Connect = 20] = "Connect", e[e.Ping = 21] = "Ping", e[e.ReadFromRadio = 22] = "ReadFromRadio", e[e.WriteToRadio = 23] = "WriteToRadio", e[e.SetDebugMode = 24] = "SetDebugMode", e[e.GetMetadata = 25] = "GetMetadata", e[e.ResetNodes = 26] = "ResetNodes", e[e.Shutdown = 27] = "Shutdown", e[e.Reboot = 28] = "Reboot", e[e.RebootOta = 29] = "RebootOta", e[e.FactoryReset = 30] = "FactoryReset", e[e.EnterDfuMode = 31] = "EnterDfuMode", e[e.RemoveNodeByNum = 32] = "RemoveNodeByNum", e[e.SetCannedMessages = 33] = "SetCannedMessages", e[e.Disconnect = 34] = "Disconnect", e[e.ConnectionStatus = 35] = "ConnectionStatus", e;
}({}), dd = /* @__PURE__ */ function(e) {
	return e[e.Primary = 0] = "Primary", e[e.Channel1 = 1] = "Channel1", e[e.Channel2 = 2] = "Channel2", e[e.Channel3 = 3] = "Channel3", e[e.Channel4 = 4] = "Channel4", e[e.Channel5 = 5] = "Channel5", e[e.Channel6 = 6] = "Channel6", e[e.Admin = 7] = "Admin", e;
}({}), fd = /* @__PURE__ */ h({ "../../node_modules/.pnpm/ste-core@3.0.11/node_modules/ste-core/dist/dispatching/DispatcherWrapper.js": ((e) => {
	Object.defineProperty(e, "__esModule", { value: !0 }), e.DispatcherWrapper = class {
		constructor(e) {
			this._subscribe = (t) => e.subscribe(t), this._unsubscribe = (t) => e.unsubscribe(t), this._one = (t) => e.one(t), this._has = (t) => e.has(t), this._clear = () => e.clear(), this._count = () => e.count, this._onSubscriptionChange = () => e.onSubscriptionChange;
		}
		get onSubscriptionChange() {
			return this._onSubscriptionChange();
		}
		get count() {
			return this._count();
		}
		subscribe(e) {
			return this._subscribe(e);
		}
		sub(e) {
			return this.subscribe(e);
		}
		unsubscribe(e) {
			this._unsubscribe(e);
		}
		unsub(e) {
			this.unsubscribe(e);
		}
		one(e) {
			return this._one(e);
		}
		has(e) {
			return this._has(e);
		}
		clear() {
			this._clear();
		}
	};
}) }), pd = /* @__PURE__ */ h({ "../../node_modules/.pnpm/ste-core@3.0.11/node_modules/ste-core/dist/events/Subscription.js": ((e) => {
	Object.defineProperty(e, "__esModule", { value: !0 }), e.Subscription = class {
		constructor(e, t) {
			this.handler = e, this.isOnce = t, this.isExecuted = !1;
		}
		execute(e, t, n) {
			if (!this.isOnce || !this.isExecuted) {
				this.isExecuted = !0;
				var r = this.handler;
				e ? setTimeout(() => {
					r.apply(t, n);
				}, 1) : r.apply(t, n);
			}
		}
	};
}) }), md = /* @__PURE__ */ h({ "../../node_modules/.pnpm/ste-core@3.0.11/node_modules/ste-core/dist/management/EventManagement.js": ((e) => {
	Object.defineProperty(e, "__esModule", { value: !0 }), e.EventManagement = class {
		constructor(e) {
			this.unsub = e, this.propagationStopped = !1;
		}
		stopPropagation() {
			this.propagationStopped = !0;
		}
	};
}) }), hd = /* @__PURE__ */ h({ "../../node_modules/.pnpm/ste-core@3.0.11/node_modules/ste-core/dist/dispatching/DispatcherBase.js": ((e) => {
	Object.defineProperty(e, "__esModule", { value: !0 });
	let t = fd(), n = pd(), r = md();
	var i = class {
		constructor() {
			this._subscriptions = [];
		}
		get count() {
			return this._subscriptions.length;
		}
		get onSubscriptionChange() {
			return this._onSubscriptionChange ??= new a(), this._onSubscriptionChange.asEvent();
		}
		subscribe(e) {
			return e && (this._subscriptions.push(this.createSubscription(e, !1)), this.triggerSubscriptionChange()), () => {
				this.unsubscribe(e);
			};
		}
		sub(e) {
			return this.subscribe(e);
		}
		one(e) {
			return e && (this._subscriptions.push(this.createSubscription(e, !0)), this.triggerSubscriptionChange()), () => {
				this.unsubscribe(e);
			};
		}
		has(e) {
			return e ? this._subscriptions.some((t) => t.handler == e) : !1;
		}
		unsubscribe(e) {
			if (!e) return;
			let t = !1;
			for (let n = 0; n < this._subscriptions.length; n++) if (this._subscriptions[n].handler == e) {
				this._subscriptions.splice(n, 1), t = !0;
				break;
			}
			t && this.triggerSubscriptionChange();
		}
		unsub(e) {
			this.unsubscribe(e);
		}
		_dispatch(e, t, n) {
			for (let i of [...this._subscriptions]) {
				let a = new r.EventManagement(() => this.unsub(i.handler)), o = Array.prototype.slice.call(n);
				if (o.push(a), i.execute(e, t, o), this.cleanup(i), !e && a.propagationStopped) return { propagationStopped: !0 };
			}
			return e ? null : { propagationStopped: !1 };
		}
		createSubscription(e, t) {
			return new n.Subscription(e, t);
		}
		cleanup(e) {
			let t = !1;
			if (e.isOnce && e.isExecuted) {
				let n = this._subscriptions.indexOf(e);
				n > -1 && (this._subscriptions.splice(n, 1), t = !0);
			}
			t && this.triggerSubscriptionChange();
		}
		asEvent() {
			return this._wrap ??= new t.DispatcherWrapper(this), this._wrap;
		}
		clear() {
			this._subscriptions.length != 0 && (this._subscriptions.splice(0, this._subscriptions.length), this.triggerSubscriptionChange());
		}
		triggerSubscriptionChange() {
			this._onSubscriptionChange != null && this._onSubscriptionChange.dispatch(this.count);
		}
	};
	e.DispatcherBase = i;
	var a = class extends i {
		dispatch(e) {
			this._dispatch(!1, this, arguments);
		}
	};
	e.SubscriptionChangeEventDispatcher = a;
}) }), gd = /* @__PURE__ */ h({ "../../node_modules/.pnpm/ste-core@3.0.11/node_modules/ste-core/dist/dispatching/DispatchError.js": ((e) => {
	Object.defineProperty(e, "__esModule", { value: !0 }), e.DispatchError = class extends Error {
		constructor(e) {
			super(e);
		}
	};
}) }), _d = /* @__PURE__ */ h({ "../../node_modules/.pnpm/ste-core@3.0.11/node_modules/ste-core/dist/dispatching/EventListBase.js": ((e) => {
	Object.defineProperty(e, "__esModule", { value: !0 }), e.EventListBase = class {
		constructor() {
			this._events = {};
		}
		get(e) {
			let t = this._events[e];
			return t || (t = this.createDispatcher(), this._events[e] = t, t);
		}
		remove(e) {
			delete this._events[e];
		}
	};
}) }), vd = /* @__PURE__ */ h({ "../../node_modules/.pnpm/ste-core@3.0.11/node_modules/ste-core/dist/handling/HandlingBase.js": ((e) => {
	Object.defineProperty(e, "__esModule", { value: !0 }), e.HandlingBase = class {
		constructor(e) {
			this.events = e;
		}
		one(e, t) {
			this.events.get(e).one(t);
		}
		has(e, t) {
			return this.events.get(e).has(t);
		}
		subscribe(e, t) {
			this.events.get(e).subscribe(t);
		}
		sub(e, t) {
			this.subscribe(e, t);
		}
		unsubscribe(e, t) {
			this.events.get(e).unsubscribe(t);
		}
		unsub(e, t) {
			this.unsubscribe(e, t);
		}
	};
}) }), yd = /* @__PURE__ */ h({ "../../node_modules/.pnpm/ste-core@3.0.11/node_modules/ste-core/dist/events/PromiseSubscription.js": ((e) => {
	Object.defineProperty(e, "__esModule", { value: !0 }), e.PromiseSubscription = class {
		constructor(e, t) {
			this.handler = e, this.isOnce = t, this.isExecuted = !1;
		}
		async execute(e, t, n) {
			if (!this.isOnce || !this.isExecuted) {
				this.isExecuted = !0;
				var r = this.handler;
				if (e) {
					setTimeout(() => {
						r.apply(t, n);
					}, 1);
					return;
				}
				await r.apply(t, n);
			}
		}
	};
}) }), bd = /* @__PURE__ */ h({ "../../node_modules/.pnpm/ste-core@3.0.11/node_modules/ste-core/dist/dispatching/PromiseDispatcherBase.js": ((e) => {
	Object.defineProperty(e, "__esModule", { value: !0 });
	let t = yd(), n = md(), r = hd(), i = gd();
	e.PromiseDispatcherBase = class extends r.DispatcherBase {
		_dispatch(e, t, n) {
			throw new i.DispatchError("_dispatch not supported. Use _dispatchAsPromise.");
		}
		createSubscription(e, n) {
			return new t.PromiseSubscription(e, n);
		}
		async _dispatchAsPromise(e, t, r) {
			for (let i of [...this._subscriptions]) {
				let a = new n.EventManagement(() => this.unsub(i.handler)), o = Array.prototype.slice.call(r);
				if (o.push(a), await i.execute(e, t, o), this.cleanup(i), !e && a.propagationStopped) return { propagationStopped: !0 };
			}
			return e ? null : { propagationStopped: !1 };
		}
	};
}) }), xd = /* @__PURE__ */ h({ "../../node_modules/.pnpm/ste-core@3.0.11/node_modules/ste-core/dist/index.js": ((e) => {
	Object.defineProperty(e, "__esModule", { value: !0 }), e.SubscriptionChangeEventDispatcher = e.HandlingBase = e.PromiseDispatcherBase = e.PromiseSubscription = e.DispatchError = e.EventManagement = e.EventListBase = e.DispatcherWrapper = e.DispatcherBase = e.Subscription = void 0;
	let t = hd();
	Object.defineProperty(e, "DispatcherBase", {
		enumerable: !0,
		get: function() {
			return t.DispatcherBase;
		}
	}), Object.defineProperty(e, "SubscriptionChangeEventDispatcher", {
		enumerable: !0,
		get: function() {
			return t.SubscriptionChangeEventDispatcher;
		}
	});
	let n = gd();
	Object.defineProperty(e, "DispatchError", {
		enumerable: !0,
		get: function() {
			return n.DispatchError;
		}
	});
	let r = fd();
	Object.defineProperty(e, "DispatcherWrapper", {
		enumerable: !0,
		get: function() {
			return r.DispatcherWrapper;
		}
	});
	let i = _d();
	Object.defineProperty(e, "EventListBase", {
		enumerable: !0,
		get: function() {
			return i.EventListBase;
		}
	});
	let a = md();
	Object.defineProperty(e, "EventManagement", {
		enumerable: !0,
		get: function() {
			return a.EventManagement;
		}
	});
	let o = vd();
	Object.defineProperty(e, "HandlingBase", {
		enumerable: !0,
		get: function() {
			return o.HandlingBase;
		}
	});
	let s = bd();
	Object.defineProperty(e, "PromiseDispatcherBase", {
		enumerable: !0,
		get: function() {
			return s.PromiseDispatcherBase;
		}
	});
	let c = yd();
	Object.defineProperty(e, "PromiseSubscription", {
		enumerable: !0,
		get: function() {
			return c.PromiseSubscription;
		}
	});
	let l = pd();
	Object.defineProperty(e, "Subscription", {
		enumerable: !0,
		get: function() {
			return l.Subscription;
		}
	});
}) }), Sd = /* @__PURE__ */ h({ "../../node_modules/.pnpm/ste-simple-events@3.0.11/node_modules/ste-simple-events/dist/SimpleEventDispatcher.js": ((e) => {
	Object.defineProperty(e, "__esModule", { value: !0 });
	let t = xd();
	e.SimpleEventDispatcher = class extends t.DispatcherBase {
		constructor() {
			super();
		}
		dispatch(e) {
			let n = this._dispatch(!1, this, arguments);
			if (n == null) throw new t.DispatchError("Got `null` back from dispatch.");
			return n;
		}
		dispatchAsync(e) {
			this._dispatch(!0, this, arguments);
		}
		asEvent() {
			return super.asEvent();
		}
	};
}) }), Cd = /* @__PURE__ */ h({ "../../node_modules/.pnpm/ste-simple-events@3.0.11/node_modules/ste-simple-events/dist/SimpleEventList.js": ((e) => {
	Object.defineProperty(e, "__esModule", { value: !0 });
	let t = xd(), n = Sd();
	e.SimpleEventList = class extends t.EventListBase {
		constructor() {
			super();
		}
		createDispatcher() {
			return new n.SimpleEventDispatcher();
		}
	};
}) }), wd = /* @__PURE__ */ h({ "../../node_modules/.pnpm/ste-simple-events@3.0.11/node_modules/ste-simple-events/dist/SimpleEventHandlingBase.js": ((e) => {
	Object.defineProperty(e, "__esModule", { value: !0 });
	let t = xd(), n = Cd();
	e.SimpleEventHandlingBase = class extends t.HandlingBase {
		constructor() {
			super(new n.SimpleEventList());
		}
	};
}) }), Td = /* @__PURE__ */ h({ "../../node_modules/.pnpm/ste-simple-events@3.0.11/node_modules/ste-simple-events/dist/NonUniformSimpleEventList.js": ((e) => {
	Object.defineProperty(e, "__esModule", { value: !0 });
	let t = Sd();
	e.NonUniformSimpleEventList = class {
		constructor() {
			this._events = {};
		}
		get(e) {
			if (this._events[e]) return this._events[e];
			let t = this.createDispatcher();
			return this._events[e] = t, t;
		}
		remove(e) {
			delete this._events[e];
		}
		createDispatcher() {
			return new t.SimpleEventDispatcher();
		}
	};
}) }), Ed = /* @__PURE__ */ h({ "../../node_modules/.pnpm/ste-simple-events@3.0.11/node_modules/ste-simple-events/dist/index.js": ((e) => {
	Object.defineProperty(e, "__esModule", { value: !0 }), e.NonUniformSimpleEventList = e.SimpleEventList = e.SimpleEventHandlingBase = e.SimpleEventDispatcher = void 0;
	let t = Sd();
	Object.defineProperty(e, "SimpleEventDispatcher", {
		enumerable: !0,
		get: function() {
			return t.SimpleEventDispatcher;
		}
	});
	let n = wd();
	Object.defineProperty(e, "SimpleEventHandlingBase", {
		enumerable: !0,
		get: function() {
			return n.SimpleEventHandlingBase;
		}
	});
	let r = Td();
	Object.defineProperty(e, "NonUniformSimpleEventList", {
		enumerable: !0,
		get: function() {
			return r.NonUniformSimpleEventList;
		}
	});
	let i = Cd();
	Object.defineProperty(e, "SimpleEventList", {
		enumerable: !0,
		get: function() {
			return i.SimpleEventList;
		}
	});
}) }), z = /* @__PURE__ */ Kn(Ed(), 1), Dd = class {
	constructor() {
		this.onLogEvent = new z.SimpleEventDispatcher(), this.onFromRadio = new z.SimpleEventDispatcher(), this.onMeshPacket = new z.SimpleEventDispatcher(), this.onMyNodeInfo = new z.SimpleEventDispatcher(), this.onNodeInfoPacket = new z.SimpleEventDispatcher(), this.onChannelPacket = new z.SimpleEventDispatcher(), this.onConfigPacket = new z.SimpleEventDispatcher(), this.onModuleConfigPacket = new z.SimpleEventDispatcher(), this.onAtakPacket = new z.SimpleEventDispatcher(), this.onMessagePacket = new z.SimpleEventDispatcher(), this.onRemoteHardwarePacket = new z.SimpleEventDispatcher(), this.onPositionPacket = new z.SimpleEventDispatcher(), this.onUserPacket = new z.SimpleEventDispatcher(), this.onRoutingPacket = new z.SimpleEventDispatcher(), this.onDeviceMetadataPacket = new z.SimpleEventDispatcher(), this.onCannedMessageModulePacket = new z.SimpleEventDispatcher(), this.onWaypointPacket = new z.SimpleEventDispatcher(), this.onAudioPacket = new z.SimpleEventDispatcher(), this.onDetectionSensorPacket = new z.SimpleEventDispatcher(), this.onPingPacket = new z.SimpleEventDispatcher(), this.onIpTunnelPacket = new z.SimpleEventDispatcher(), this.onPaxcounterPacket = new z.SimpleEventDispatcher(), this.onSerialPacket = new z.SimpleEventDispatcher(), this.onStoreForwardPacket = new z.SimpleEventDispatcher(), this.onRangeTestPacket = new z.SimpleEventDispatcher(), this.onTelemetryPacket = new z.SimpleEventDispatcher(), this.onZpsPacket = new z.SimpleEventDispatcher(), this.onSimulatorPacket = new z.SimpleEventDispatcher(), this.onTraceRoutePacket = new z.SimpleEventDispatcher(), this.onNeighborInfoPacket = new z.SimpleEventDispatcher(), this.onAtakPluginPacket = new z.SimpleEventDispatcher(), this.onMapReportPacket = new z.SimpleEventDispatcher(), this.onPrivatePacket = new z.SimpleEventDispatcher(), this.onAtakForwarderPacket = new z.SimpleEventDispatcher(), this.onClientNotificationPacket = new z.SimpleEventDispatcher(), this.onDeviceStatus = new z.SimpleEventDispatcher(), this.onLogRecord = new z.SimpleEventDispatcher(), this.onMeshHeartbeat = new z.SimpleEventDispatcher(), this.onDeviceDebugLog = new z.SimpleEventDispatcher(), this.onPendingSettingsChange = new z.SimpleEventDispatcher(), this.onQueueStatus = new z.SimpleEventDispatcher();
	}
}, Od = /* @__PURE__ */ Kn(Ed(), 1), kd = class {
	constructor() {
		this.queue = [], this.lock = !1, this.ackNotifier = new Od.SimpleEventDispatcher(), this.errorNotifier = new Od.SimpleEventDispatcher(), this.timeout = 6e4;
	}
	getState() {
		return this.queue;
	}
	clear() {
		this.queue = [];
	}
	push(e) {
		let t = {
			...e,
			sent: !1,
			added: /* @__PURE__ */ new Date(),
			promise: new Promise((t, n) => {
				this.ackNotifier.subscribe((n) => {
					e.id === n && (this.remove(e.id), t(n));
				}), this.errorNotifier.subscribe((t) => {
					e.id === t.id && (this.remove(e.id), n(t));
				}), setTimeout(() => {
					if (this.queue.findIndex((t) => t.id === e.id) !== -1) {
						this.remove(e.id);
						let r = w(N.ToRadioSchema, e.data);
						if (r.payloadVariant.case === "heartbeat" || r.payloadVariant.case === "wantConfigId") {
							t(e.id);
							return;
						}
						console.warn(`Packet ${e.id} of type ${r.payloadVariant.case} timed out`), n({
							id: e.id,
							error: N.Routing_Error.TIMEOUT
						});
					}
				}, this.timeout);
			})
		};
		this.queue.push(t);
	}
	remove(e) {
		this.lock ? setTimeout(() => this.remove(e), 100) : this.queue = this.queue.filter((t) => t.id !== e);
	}
	processAck(e) {
		this.ackNotifier.dispatch(e);
	}
	processError(e) {
		console.error(`Error received for packet ${e.id}: ${N.Routing_Error[e.error]}`), this.errorNotifier.dispatch(e);
	}
	wait(e) {
		let t = this.queue.find((t) => t.id === e);
		if (!t) throw Error("Packet does not exist");
		return t.promise;
	}
	async processQueue(e) {
		if (this.lock) return;
		this.lock = !0;
		let t = e.getWriter();
		try {
			for (; this.queue.filter((e) => !e.sent).length > 0;) {
				let e = this.queue.filter((e) => !e.sent)[0];
				if (e) {
					await new Promise((e) => setTimeout(e, 200));
					try {
						await t.write(e.data), e.sent = !0;
					} catch (n) {
						if (n?.code === "ECONNRESET" || n?.code === "ERR_INVALID_STATE") throw t.releaseLock(), this.lock = !1, n;
						console.error(`Error sending packet ${e.id}`, n);
					}
				}
			}
		} finally {
			t.releaseLock(), this.lock = !1;
		}
	}
}, Ad = () => {
	let e = new Uint8Array([]), t = new TextDecoder();
	return new TransformStream({ transform(n, r) {
		e = new Uint8Array([...e, ...n]);
		let i = !1;
		for (; e.length !== 0 && !i;) {
			let n = e.findIndex((e) => e === 148);
			if (e[n + 1] === 195) {
				e.subarray(0, n).length && (r.enqueue({
					type: "debug",
					data: t.decode(e.subarray(0, n))
				}), e = e.subarray(n));
				let a = e[2], o = e[3];
				if (a !== void 0 && o !== void 0 && e.length >= 4 + (a << 8) + o) {
					let t = e.subarray(4, 4 + (a << 8) + o), n = t.findIndex((e) => e === 148);
					n !== -1 && t[n + 1] === 195 ? (console.warn(`⚠️ Malformed packet found, discarding: ${e.subarray(0, n - 1).toString()}`), e = e.subarray(n)) : (e = e.subarray(3 + (a << 8) + o + 1), r.enqueue({
						type: "packet",
						data: t
					}));
				} else i = !0;
			} else i = !0;
		}
	} });
}, jd = () => new TransformStream({ transform(e, t) {
	let n = e.length, r = new Uint8Array([
		148,
		195,
		n >> 8 & 255,
		n & 255
	]);
	t.enqueue(new Uint8Array([...r, ...e]));
} }), Md = class {
	constructor(e) {
		this.sendRaw = e, this.rxBuffer = [], this.txBuffer = [], this.textEncoder = new TextEncoder(), this.counter = 0;
	}
	async downloadFile(e) {
		return await this.sendCommand(M.XModem_Control.STX, this.textEncoder.encode(e), 0);
	}
	async uploadFile(e, t) {
		for (let e = 0; e < t.length; e += 128) this.txBuffer.push(t.slice(e, e + 128));
		return await this.sendCommand(M.XModem_Control.SOH, this.textEncoder.encode(e), 0);
	}
	async sendCommand(e, t, n, r) {
		let i = x(N.ToRadioSchema, { payloadVariant: {
			case: "xmodemPacket",
			value: {
				buffer: t,
				control: e,
				seq: n,
				crc16: r
			}
		} });
		return await this.sendRaw(S(N.ToRadioSchema, i));
	}
	async handlePacket(e) {
		switch (await new Promise((e) => setTimeout(e, 100)), e.control) {
			case M.XModem_Control.NUL: break;
			case M.XModem_Control.SOH: return this.counter = e.seq, this.validateCrc16(e) ? (this.rxBuffer[this.counter] = e.buffer, this.sendCommand(M.XModem_Control.ACK)) : await this.sendCommand(M.XModem_Control.NAK, void 0, e.seq);
			case M.XModem_Control.STX: break;
			case M.XModem_Control.EOT: break;
			case M.XModem_Control.ACK:
				if (this.counter++, this.txBuffer[this.counter - 1]) return this.sendCommand(M.XModem_Control.SOH, this.txBuffer[this.counter - 1], this.counter, Qn(this.txBuffer[this.counter - 1] ?? /* @__PURE__ */ new Uint8Array()));
				if (this.counter === this.txBuffer.length + 1) return this.sendCommand(M.XModem_Control.EOT);
				this.clear();
				break;
			case M.XModem_Control.NAK: return this.sendCommand(M.XModem_Control.SOH, this.txBuffer[this.counter], this.counter, Qn(this.txBuffer[this.counter - 1] ?? /* @__PURE__ */ new Uint8Array()));
			case M.XModem_Control.CAN:
				this.clear();
				break;
			case M.XModem_Control.CTRLZ:
		}
		return Promise.resolve(0);
	}
	validateCrc16(e) {
		return Qn(e.buffer) === e.crc16;
	}
	clear() {
		this.counter = 0, this.rxBuffer = [], this.txBuffer = [];
	}
}, Nd = Wn({
	EventSystem: () => Dd,
	Queue: () => kd,
	Xmodem: () => Md,
	fromDeviceStream: () => Ad,
	toDeviceStream: () => jd
}), Pd = (e) => new WritableStream({ write(t) {
	switch (t.type) {
		case "status": {
			let { status: n, reason: r } = t.data;
			e.updateDeviceStatus(n), e.log.info(R[R.ConnectionStatus], `🔗 ${ld[n]} ${r ? `(${r})` : ""}`);
			break;
		}
		case "debug": break;
		case "packet": {
			let n;
			try {
				n = w(N.FromRadioSchema, t.data);
			} catch (t) {
				e.log.error(R[R.HandleFromRadio], "⚠️  Received undecodable packet", t);
				break;
			}
			switch (e.events.onFromRadio.dispatch(n), n.payloadVariant.case) {
				case "packet":
					try {
						e.handleMeshPacket(n.payloadVariant.value);
					} catch (t) {
						e.log.error(R[R.HandleFromRadio], "⚠️  Unable to handle mesh packet", t);
					}
					break;
				case "myInfo":
					e.events.onMyNodeInfo.dispatch(n.payloadVariant.value), e.log.info(R[R.HandleFromRadio], "📱 Received Node info for this device");
					break;
				case "nodeInfo":
					e.log.info(R[R.HandleFromRadio], `📱 Received Node Info packet for node: ${n.payloadVariant.value.num}`), e.events.onNodeInfoPacket.dispatch(n.payloadVariant.value), n.payloadVariant.value.position && e.events.onPositionPacket.dispatch({
						id: n.id,
						rxTime: /* @__PURE__ */ new Date(),
						from: n.payloadVariant.value.num,
						to: n.payloadVariant.value.num,
						type: "direct",
						channel: dd.Primary,
						data: n.payloadVariant.value.position
					}), n.payloadVariant.value.user && e.events.onUserPacket.dispatch({
						id: n.id,
						rxTime: /* @__PURE__ */ new Date(),
						from: n.payloadVariant.value.num,
						to: n.payloadVariant.value.num,
						type: "direct",
						channel: dd.Primary,
						data: n.payloadVariant.value.user
					});
					break;
				case "config":
					n.payloadVariant.value.payloadVariant.case ? e.log.trace(R[R.HandleFromRadio], `💾 Received Config packet of variant: ${n.payloadVariant.value.payloadVariant.case}`) : e.log.warn(R[R.HandleFromRadio], "⚠️ Received Config packet of variant: UNK"), e.events.onConfigPacket.dispatch(n.payloadVariant.value);
					break;
				case "logRecord":
					e.log.trace(R[R.HandleFromRadio], "Received onLogRecord"), e.events.onLogRecord.dispatch(n.payloadVariant.value);
					break;
				case "configCompleteId":
					n.payloadVariant.value !== e.configId && e.log.error(R[R.HandleFromRadio], `❌ Invalid config id received from device, expected ${e.configId} but received ${n.payloadVariant.value}`), e.log.info(R[R.HandleFromRadio], `⚙️ Valid config id received from device: ${e.configId}`), e.updateDeviceStatus(ld.DeviceConfigured);
					break;
				case "rebooted":
					e.configure().catch(() => {});
					break;
				case "moduleConfig":
					n.payloadVariant.value.payloadVariant.case ? e.log.trace(R[R.HandleFromRadio], `💾 Received Module Config packet of variant: ${n.payloadVariant.value.payloadVariant.case}`) : e.log.warn(R[R.HandleFromRadio], "⚠️ Received Module Config packet of variant: UNK"), e.events.onModuleConfigPacket.dispatch(n.payloadVariant.value);
					break;
				case "channel":
					e.log.trace(R[R.HandleFromRadio], `🔐 Received Channel: ${n.payloadVariant.value.index}`), e.events.onChannelPacket.dispatch(n.payloadVariant.value);
					break;
				case "queueStatus":
					e.log.trace(R[R.HandleFromRadio], `🚧 Received Queue Status: ${n.payloadVariant.value}`), e.events.onQueueStatus.dispatch(n.payloadVariant.value);
					break;
				case "xmodemPacket":
					e.xModem.handlePacket(n.payloadVariant.value);
					break;
				case "metadata":
					Number.parseFloat(n.payloadVariant.value.firmwareVersion) < Gu.minFwVer && e.log.fatal(R[R.HandleFromRadio], `Device firmware outdated. Min supported: ${Gu.minFwVer} got : ${n.payloadVariant.value.firmwareVersion}`), e.log.debug(R[R.GetMetadata], "🏷️ Received metadata packet"), e.events.onDeviceMetadataPacket.dispatch({
						id: n.id,
						rxTime: /* @__PURE__ */ new Date(),
						from: 0,
						to: 0,
						type: "direct",
						channel: dd.Primary,
						data: n.payloadVariant.value
					});
					break;
				case "mqttClientProxyMessage": break;
				case "clientNotification":
					e.log.trace(R[R.HandleFromRadio], `📣 Received ClientNotification: ${n.payloadVariant.value.message}`), e.events.onClientNotificationPacket.dispatch(n.payloadVariant.value);
					break;
				default: e.log.warn(R[R.HandleFromRadio], `⚠️ Unhandled payload variant: ${n.payloadVariant.case}`);
			}
		}
	}
} }), Fd = class {
	constructor(e, t) {
		this.log = new cd({
			name: "iMeshDevice",
			prettyLogTemplate: "{{hh}}:{{MM}}:{{ss}}:{{ms}}	{{logLevelName}}	[{{name}}]	"
		}), this.transport = e, this.deviceStatus = ld.DeviceDisconnected, this.isConfigured = !1, this.pendingSettingsChanges = !1, this.myNodeInfo = x(N.MyNodeInfoSchema), this.configId = t ?? this.generateRandId(), this.queue = new kd(), this.events = new Dd(), this.xModem = new Md(this.sendRaw.bind(this)), this.events.onDeviceStatus.subscribe((e) => {
			this.deviceStatus = e, e === ld.DeviceConfigured ? this.isConfigured = !0 : e === ld.DeviceConfiguring ? this.isConfigured = !1 : e === ld.DeviceDisconnected && (this._heartbeatIntervalId !== void 0 && clearInterval(this._heartbeatIntervalId), this.complete());
		}), this.events.onMyNodeInfo.subscribe((e) => {
			this.myNodeInfo = e;
		}), this.events.onPendingSettingsChange.subscribe((e) => {
			this.pendingSettingsChanges = e;
		}), this.transport.fromDevice.pipeTo(Pd(this));
	}
	async sendText(e, t, n, r, i, a) {
		this.log.debug(R[R.SendText], `📤 Sending message to ${t ?? "broadcast"} on channel ${r?.toString() ?? 0}`);
		let o = new TextEncoder();
		return await this.sendPacket(o.encode(e), A.PortNum.TEXT_MESSAGE_APP, t ?? "broadcast", r, n, !1, !0, i, a);
	}
	sendWaypoint(e, t, n) {
		return this.log.debug(R[R.SendWaypoint], `📤 Sending waypoint to ${t} on channel ${n?.toString() ?? 0}`), e.id = this.generateRandId(), this.sendPacket(S(N.WaypointSchema, e), A.PortNum.WAYPOINT_APP, t, n, !0, !1);
	}
	async sendPacket(e, t, n, r = dd.Primary, i = !0, a = !0, o = !1, s, c) {
		this.log.trace(R[R.SendPacket], `📤 Sending ${A.PortNum[t]} to ${n}`);
		let l = x(N.MeshPacketSchema, {
			payloadVariant: {
				case: "decoded",
				value: {
					payload: e,
					portnum: t,
					wantResponse: a,
					emoji: c,
					replyId: s,
					dest: 0,
					requestId: 0,
					source: 0
				}
			},
			from: this.myNodeInfo.myNodeNum,
			to: n === "broadcast" ? Gu.broadcastNum : n === "self" ? this.myNodeInfo.myNodeNum : n,
			id: this.generateRandId(),
			wantAck: i,
			channel: r
		}), u = x(N.ToRadioSchema, { payloadVariant: {
			case: "packet",
			value: l
		} });
		return o && (l.rxTime = Math.trunc(Date.now() / 1e3), this.handleMeshPacket(l)), await this.sendRaw(S(N.ToRadioSchema, u), l.id);
	}
	async sendRaw(e, t = this.generateRandId()) {
		if (e.length > 512) throw Error("Message longer than 512 bytes, it will not be sent!");
		return this.queue.push({
			id: t,
			data: e
		}), await this.queue.processQueue(this.transport.toDevice), this.queue.wait(t);
	}
	async setConfig(e) {
		this.log.debug(R[R.SetConfig], `⚙️ Setting config, Variant: ${e.payloadVariant.case ?? "Unknown"}`), this.pendingSettingsChanges || await this.beginEditSettings();
		let t = x(F.AdminMessageSchema, { payloadVariant: {
			case: "setConfig",
			value: e
		} });
		return this.sendPacket(S(F.AdminMessageSchema, t), A.PortNum.ADMIN_APP, "self");
	}
	async setModuleConfig(e) {
		this.log.debug(R[R.SetModuleConfig], "⚙️ Setting module config");
		let t = x(F.AdminMessageSchema, { payloadVariant: {
			case: "setModuleConfig",
			value: e
		} });
		return await this.sendPacket(S(F.AdminMessageSchema, t), A.PortNum.ADMIN_APP, "self");
	}
	async setCannedMessages(e) {
		this.log.debug(R[R.SetCannedMessages], "⚙️ Setting CannedMessages");
		let t = x(F.AdminMessageSchema, { payloadVariant: {
			case: "setCannedMessageModuleMessages",
			value: e.messages
		} });
		return await this.sendPacket(S(F.AdminMessageSchema, t), A.PortNum.ADMIN_APP, "self");
	}
	async setOwner(e) {
		this.log.debug(R[R.SetOwner], "👤 Setting owner");
		let t = x(F.AdminMessageSchema, { payloadVariant: {
			case: "setOwner",
			value: e
		} });
		return await this.sendPacket(S(F.AdminMessageSchema, t), A.PortNum.ADMIN_APP, "self");
	}
	async setChannel(e) {
		this.log.debug(R[R.SetChannel], `📻 Setting Channel: ${e.index}`);
		let t = x(F.AdminMessageSchema, { payloadVariant: {
			case: "setChannel",
			value: e
		} });
		return await this.sendPacket(S(F.AdminMessageSchema, t), A.PortNum.ADMIN_APP, "self");
	}
	async enterDfuMode() {
		this.log.debug(R[R.EnterDfuMode], "🔌 Entering DFU mode");
		let e = x(F.AdminMessageSchema, { payloadVariant: {
			case: "enterDfuModeRequest",
			value: !0
		} });
		return await this.sendPacket(S(F.AdminMessageSchema, e), A.PortNum.ADMIN_APP, "self");
	}
	async setPosition(e) {
		return await this.sendPacket(S(N.PositionSchema, e), A.PortNum.POSITION_APP, "self");
	}
	async setFixedPosition(e, t) {
		let n = x(F.AdminMessageSchema, { payloadVariant: {
			case: "setFixedPosition",
			value: x(N.PositionSchema, {
				latitudeI: Math.floor(e / 1e-7),
				longitudeI: Math.floor(t / 1e-7)
			})
		} });
		return await this.sendPacket(S(F.AdminMessageSchema, n), A.PortNum.ADMIN_APP, "self", 0, !0, !1);
	}
	async removeFixedPosition() {
		let e = x(F.AdminMessageSchema, { payloadVariant: {
			case: "removeFixedPosition",
			value: !0
		} });
		return await this.sendPacket(S(F.AdminMessageSchema, e), A.PortNum.ADMIN_APP, "self", 0, !0, !1);
	}
	async getChannel(e) {
		this.log.debug(R[R.GetChannel], `📻 Requesting Channel: ${e}`);
		let t = x(F.AdminMessageSchema, { payloadVariant: {
			case: "getChannelRequest",
			value: e + 1
		} });
		return await this.sendPacket(S(F.AdminMessageSchema, t), A.PortNum.ADMIN_APP, "self");
	}
	async getConfig(e) {
		this.log.debug(R[R.GetConfig], "⚙️ Requesting config");
		let t = x(F.AdminMessageSchema, { payloadVariant: {
			case: "getConfigRequest",
			value: e
		} });
		return await this.sendPacket(S(F.AdminMessageSchema, t), A.PortNum.ADMIN_APP, "self");
	}
	async getModuleConfig(e) {
		this.log.debug(R[R.GetModuleConfig], "⚙️ Requesting module config");
		let t = x(F.AdminMessageSchema, { payloadVariant: {
			case: "getModuleConfigRequest",
			value: e
		} });
		return await this.sendPacket(S(F.AdminMessageSchema, t), A.PortNum.ADMIN_APP, "self");
	}
	async getOwner() {
		this.log.debug(R[R.GetOwner], "👤 Requesting owner");
		let e = x(F.AdminMessageSchema, { payloadVariant: {
			case: "getOwnerRequest",
			value: !0
		} });
		return await this.sendPacket(S(F.AdminMessageSchema, e), A.PortNum.ADMIN_APP, "self");
	}
	async getMetadata(e) {
		this.log.debug(R[R.GetMetadata], `🏷️ Requesting metadata from ${e}`);
		let t = x(F.AdminMessageSchema, { payloadVariant: {
			case: "getDeviceMetadataRequest",
			value: !0
		} });
		return await this.sendPacket(S(F.AdminMessageSchema, t), A.PortNum.ADMIN_APP, e, dd.Admin);
	}
	async clearChannel(e) {
		this.log.debug(R[R.ClearChannel], `📻 Clearing Channel ${e}`);
		let t = x(No.ChannelSchema, {
			index: e,
			role: No.Channel_Role.DISABLED
		}), n = x(F.AdminMessageSchema, { payloadVariant: {
			case: "setChannel",
			value: t
		} });
		return await this.sendPacket(S(F.AdminMessageSchema, n), A.PortNum.ADMIN_APP, "self");
	}
	async beginEditSettings() {
		this.events.onPendingSettingsChange.dispatch(!0);
		let e = x(F.AdminMessageSchema, { payloadVariant: {
			case: "beginEditSettings",
			value: !0
		} });
		return await this.sendPacket(S(F.AdminMessageSchema, e), A.PortNum.ADMIN_APP, "self");
	}
	async commitEditSettings() {
		this.events.onPendingSettingsChange.dispatch(!1);
		let e = x(F.AdminMessageSchema, { payloadVariant: {
			case: "commitEditSettings",
			value: !0
		} });
		return await this.sendPacket(S(F.AdminMessageSchema, e), A.PortNum.ADMIN_APP, "self");
	}
	async resetNodes() {
		this.log.debug(R[R.ResetNodes], "📻 Resetting NodeDB");
		let e = x(F.AdminMessageSchema, { payloadVariant: {
			case: "nodedbReset",
			value: 1
		} });
		return await this.sendPacket(S(F.AdminMessageSchema, e), A.PortNum.ADMIN_APP, "self");
	}
	async removeNodeByNum(e) {
		this.log.debug(R[R.RemoveNodeByNum], `📻 Removing Node ${e} from NodeDB`);
		let t = x(F.AdminMessageSchema, { payloadVariant: {
			case: "removeByNodenum",
			value: e
		} });
		return await this.sendPacket(S(F.AdminMessageSchema, t), A.PortNum.ADMIN_APP, "self");
	}
	async shutdown(e) {
		this.log.debug(R[R.Shutdown], `🔌 Shutting down ${e > 2 ? "now" : `in ${e} seconds`}`);
		let t = x(F.AdminMessageSchema, { payloadVariant: {
			case: "shutdownSeconds",
			value: e
		} });
		return await this.sendPacket(S(F.AdminMessageSchema, t), A.PortNum.ADMIN_APP, "self");
	}
	async reboot(e) {
		this.log.debug(R[R.Reboot], `🔌 Rebooting node ${e === 0 ? "now" : `in ${e} seconds`}`);
		let t = x(F.AdminMessageSchema, { payloadVariant: {
			case: "rebootSeconds",
			value: e
		} });
		return await this.sendPacket(S(F.AdminMessageSchema, t), A.PortNum.ADMIN_APP, "self");
	}
	async rebootOta(e) {
		this.log.debug(R[R.RebootOta], `🔌 Rebooting into OTA mode ${e === 0 ? "now" : `in ${e} seconds`}`);
		let t = x(F.AdminMessageSchema, { payloadVariant: {
			case: "rebootOtaSeconds",
			value: e
		} });
		return await this.sendPacket(S(F.AdminMessageSchema, t), A.PortNum.ADMIN_APP, "self");
	}
	async factoryResetDevice() {
		this.log.debug(R[R.FactoryReset], "♻️ Factory resetting device");
		let e = x(F.AdminMessageSchema, { payloadVariant: {
			case: "factoryResetDevice",
			value: 1
		} });
		return await this.sendPacket(S(F.AdminMessageSchema, e), A.PortNum.ADMIN_APP, "self");
	}
	async factoryResetConfig() {
		this.log.debug(R[R.FactoryReset], "♻️ Factory resetting config");
		let e = x(F.AdminMessageSchema, { payloadVariant: {
			case: "factoryResetConfig",
			value: 1
		} });
		return await this.sendPacket(S(F.AdminMessageSchema, e), A.PortNum.ADMIN_APP, "self");
	}
	configure() {
		this.log.debug(R[R.Configure], "⚙️ Requesting device configuration"), this.updateDeviceStatus(ld.DeviceConfiguring);
		let e = x(N.ToRadioSchema, { payloadVariant: {
			case: "wantConfigId",
			value: this.configId
		} });
		return this.sendRaw(S(N.ToRadioSchema, e)).catch((e) => {
			throw this.deviceStatus === ld.DeviceDisconnected ? Error("Device connection lost") : e;
		});
	}
	heartbeat() {
		this.log.debug(R[R.Ping], "❤️ Send heartbeat ping to radio");
		let e = x(N.ToRadioSchema, { payloadVariant: {
			case: "heartbeat",
			value: {}
		} });
		return this.sendRaw(S(N.ToRadioSchema, e));
	}
	setHeartbeatInterval(e) {
		this._heartbeatIntervalId !== void 0 && clearInterval(this._heartbeatIntervalId), this._heartbeatIntervalId = setInterval(() => {
			this.heartbeat().catch((e) => {
				this.log.error(R[R.Ping], `⚠️ Unable to send heartbeat: ${e.message}`);
			});
		}, e);
	}
	async traceRoute(e) {
		let t = x(N.RouteDiscoverySchema, { route: [] });
		return await this.sendPacket(S(N.RouteDiscoverySchema, t), A.PortNum.TRACEROUTE_APP, e);
	}
	async requestPosition(e) {
		return await this.sendPacket(/* @__PURE__ */ new Uint8Array(), A.PortNum.POSITION_APP, e);
	}
	updateDeviceStatus(e) {
		e !== this.deviceStatus && this.events.onDeviceStatus.dispatch(e);
	}
	generateRandId() {
		let e = crypto.getRandomValues(/* @__PURE__ */ new Uint32Array(1));
		if (!e[0]) throw Error("Cannot generate CSPRN");
		return Math.floor(e[0] * 2 ** -32 * 1e9);
	}
	complete() {
		this.queue.clear();
	}
	async disconnect() {
		this.log.debug(R[R.Disconnect], "🔌 Disconnecting from device"), this._heartbeatIntervalId !== void 0 && clearInterval(this._heartbeatIntervalId), this.complete(), await this.transport.toDevice.close(), await this.transport.disconnect();
	}
	handleMeshPacket(e) {
		switch (this.events.onMeshPacket.dispatch(e), e.from !== this.myNodeInfo.myNodeNum && this.events.onMeshHeartbeat.dispatch(/* @__PURE__ */ new Date()), e.payloadVariant.case) {
			case "decoded":
				this.handleDecodedPacket(e.payloadVariant.value, e);
				break;
			case "encrypted":
				this.log.debug(R[R.HandleMeshPacket], "🔐 Device received encrypted data packet, ignoring.");
				break;
			default: throw Error(`Unhandled case ${e.payloadVariant.case}`);
		}
	}
	handleDecodedPacket(e, t) {
		let n, r, i = {
			id: t.id,
			rxTime: /* @__PURE__ */ new Date(t.rxTime * 1e3),
			type: t.to === Gu.broadcastNum ? "broadcast" : "direct",
			from: t.from,
			to: t.to,
			channel: t.channel
		};
		switch (this.log.trace(R[R.HandleMeshPacket], `📦 Received ${A.PortNum[e.portnum]} packet`), e.portnum) {
			case A.PortNum.TEXT_MESSAGE_APP:
				this.events.onMessagePacket.dispatch({
					...i,
					data: new TextDecoder().decode(e.payload)
				});
				break;
			case A.PortNum.REMOTE_HARDWARE_APP:
				this.events.onRemoteHardwarePacket.dispatch({
					...i,
					data: w(ku.HardwareMessageSchema, e.payload)
				});
				break;
			case A.PortNum.POSITION_APP:
				this.events.onPositionPacket.dispatch({
					...i,
					data: w(N.PositionSchema, e.payload)
				});
				break;
			case A.PortNum.NODEINFO_APP:
				this.events.onUserPacket.dispatch({
					...i,
					data: w(N.UserSchema, e.payload)
				});
				break;
			case A.PortNum.ROUTING_APP:
				switch (r = w(N.RoutingSchema, e.payload), this.events.onRoutingPacket.dispatch({
					...i,
					data: r
				}), r.variant.case) {
					case "errorReason":
						r.variant.value === N.Routing_Error.NONE ? this.queue.processAck(e.requestId) : this.queue.processError({
							id: e.requestId,
							error: r.variant.value
						});
						break;
					case "routeReply": break;
					case "routeRequest": break;
					default: throw Error(`Unhandled case ${r.variant.case}`);
				}
				break;
			case A.PortNum.ADMIN_APP:
				switch (n = w(F.AdminMessageSchema, e.payload), n.payloadVariant.case) {
					case "getChannelResponse":
						this.events.onChannelPacket.dispatch(n.payloadVariant.value);
						break;
					case "getOwnerResponse":
						this.events.onUserPacket.dispatch({
							...i,
							data: n.payloadVariant.value
						});
						break;
					case "getConfigResponse":
						this.events.onConfigPacket.dispatch(n.payloadVariant.value);
						break;
					case "getModuleConfigResponse":
						this.events.onModuleConfigPacket.dispatch(n.payloadVariant.value);
						break;
					case "getDeviceMetadataResponse":
						this.log.debug(R[R.GetMetadata], `🏷️ Received metadata packet from ${e.source}`), this.events.onDeviceMetadataPacket.dispatch({
							...i,
							data: n.payloadVariant.value
						});
						break;
					case "getCannedMessageModuleMessagesResponse":
						this.log.debug(R[R.GetMetadata], "🥫 Received CannedMessage Module Messages response packet"), this.events.onCannedMessageModulePacket.dispatch({
							...i,
							data: n.payloadVariant.value
						});
						break;
					default: this.log.error(R[R.HandleMeshPacket], `⚠️ Received unhandled AdminMessage, type ${n.payloadVariant.case ?? "undefined"}`, e.payload);
				}
				break;
			case A.PortNum.WAYPOINT_APP:
				this.events.onWaypointPacket.dispatch({
					...i,
					data: w(N.WaypointSchema, e.payload)
				});
				break;
			case A.PortNum.AUDIO_APP:
				this.events.onAudioPacket.dispatch({
					...i,
					data: e.payload
				});
				break;
			case A.PortNum.DETECTION_SENSOR_APP:
				this.events.onDetectionSensorPacket.dispatch({
					...i,
					data: e.payload
				});
				break;
			case A.PortNum.REPLY_APP:
				this.events.onPingPacket.dispatch({
					...i,
					data: e.payload
				});
				break;
			case A.PortNum.IP_TUNNEL_APP:
				this.events.onIpTunnelPacket.dispatch({
					...i,
					data: e.payload
				});
				break;
			case A.PortNum.PAXCOUNTER_APP:
				this.events.onPaxcounterPacket.dispatch({
					...i,
					data: w(vu.PaxcountSchema, e.payload)
				});
				break;
			case A.PortNum.SERIAL_APP:
				this.events.onSerialPacket.dispatch({
					...i,
					data: e.payload
				});
				break;
			case A.PortNum.STORE_FORWARD_APP:
				this.events.onStoreForwardPacket.dispatch({
					...i,
					data: e.payload
				});
				break;
			case A.PortNum.RANGE_TEST_APP:
				this.events.onRangeTestPacket.dispatch({
					...i,
					data: e.payload
				});
				break;
			case A.PortNum.TELEMETRY_APP:
				this.events.onTelemetryPacket.dispatch({
					...i,
					data: w(hc.TelemetrySchema, e.payload)
				});
				break;
			case A.PortNum.ZPS_APP:
				this.events.onZpsPacket.dispatch({
					...i,
					data: e.payload
				});
				break;
			case A.PortNum.SIMULATOR_APP:
				this.events.onSimulatorPacket.dispatch({
					...i,
					data: e.payload
				});
				break;
			case A.PortNum.TRACEROUTE_APP:
				this.events.onTraceRoutePacket.dispatch({
					...i,
					data: w(N.RouteDiscoverySchema, e.payload)
				});
				break;
			case A.PortNum.NEIGHBORINFO_APP:
				this.events.onNeighborInfoPacket.dispatch({
					...i,
					data: w(N.NeighborInfoSchema, e.payload)
				});
				break;
			case A.PortNum.ATAK_PLUGIN:
				this.events.onAtakPluginPacket.dispatch({
					...i,
					data: e.payload
				});
				break;
			case A.PortNum.MAP_REPORT_APP:
				this.events.onMapReportPacket.dispatch({
					...i,
					data: e.payload
				});
				break;
			case A.PortNum.PRIVATE_APP:
				this.events.onPrivatePacket.dispatch({
					...i,
					data: e.payload
				});
				break;
			case A.PortNum.ATAK_FORWARDER:
				this.events.onAtakForwarderPacket.dispatch({
					...i,
					data: e.payload
				});
				break;
			default: throw Error(`Unhandled case ${e.portnum}`);
		}
	}
}, Id = class e {
	static async create(t) {
		let n = await navigator.serial.requestPort();
		return await n.open({ baudRate: t || 115200 }), new e(n);
	}
	static async createFromPort(t, n) {
		return (!t.readable || !t.writable) && await t.open({ baudRate: n || 115200 }), new e(t);
	}
	constructor(e) {
		if (this.pipePromise = null, this.lastStatus = L.DeviceStatusEnum.DeviceDisconnected, this.closingByUser = !1, !e.readable || !e.writable) throw Error("Stream not accessible");
		this.connection = e, this.portReadable = e.readable, this.abortController = new AbortController();
		let t = this.abortController, n = Nd.toDeviceStream();
		this.pipePromise = n.readable.pipeTo(e.writable, { signal: this.abortController.signal }).catch((e) => {
			t.signal.aborted || (console.error("Error piping data to serial port:", e), this.connection.close().catch(() => {}), this.emitStatus(L.DeviceStatusEnum.DeviceDisconnected, "write-error"));
		}), this._toDevice = n.writable, this._fromDevice = new ReadableStream({ start: async (e) => {
			this.fromDeviceController = e, this.emitStatus(L.DeviceStatusEnum.DeviceConnecting);
			let t = this.portReadable.pipeThrough(Nd.fromDeviceStream()), n = t.getReader(), r = (e) => {
				let { port: t } = e;
				t && t === this.connection && this.emitStatus(L.DeviceStatusEnum.DeviceDisconnected, "serial-disconnected");
			};
			navigator.serial.addEventListener("disconnect", r), this.emitStatus(L.DeviceStatusEnum.DeviceConnected);
			try {
				for (;;) {
					let { value: t, done: r } = await n.read();
					if (r) break;
					e.enqueue(t);
				}
				e.close();
			} catch (n) {
				this.closingByUser || this.emitStatus(L.DeviceStatusEnum.DeviceDisconnected, "read-error"), e.error(n instanceof Error ? n : Error(String(n)));
				try {
					await t.cancel();
				} catch {}
			} finally {
				n.releaseLock(), navigator.serial.removeEventListener("disconnect", r);
			}
		} });
	}
	get toDevice() {
		return this._toDevice;
	}
	get fromDevice() {
		return this._fromDevice;
	}
	emitStatus(e, t) {
		e !== this.lastStatus && (this.lastStatus = e, this.fromDeviceController?.enqueue({
			type: "status",
			data: {
				status: e,
				reason: t
			}
		}));
	}
	async disconnect() {
		try {
			if (this.closingByUser = !0, this.abortController.abort(), this.pipePromise && await this.pipePromise, this._fromDevice?.locked) try {
				await this._fromDevice.cancel();
			} catch {}
			await this.connection.close();
		} catch (e) {
			console.warn("Could not cleanly disconnect serial port:", e);
		} finally {
			this.emitStatus(L.DeviceStatusEnum.DeviceDisconnected, "user"), this.closingByUser = !1;
		}
	}
	async reconnect() {
		this.emitStatus(L.DeviceStatusEnum.DeviceConnecting, "reconnect");
		try {
			if (!this.connection.readable || !this.connection.writable) throw Error("Stream not accessible");
			this.portReadable = this.connection.readable, this.abortController = new AbortController();
			let e = this.abortController;
			this.pipePromise = Nd.toDeviceStream().readable.pipeTo(this.connection.writable, { signal: this.abortController.signal }).catch((t) => {
				e.signal.aborted || (console.error("Error piping data to serial port (reconnect):", t), this.emitStatus(L.DeviceStatusEnum.DeviceDisconnected, "write-error"));
			}), this.emitStatus(L.DeviceStatusEnum.DeviceConnected, "reconnected");
		} catch (e) {
			throw this.emitStatus(L.DeviceStatusEnum.DeviceDisconnected, "reconnect-failed"), e;
		}
	}
};
//#endregion
//#region node_modules/@meshtastic/transport-web-bluetooth/dist/mod.js
function Ld(e) {
	return e.buffer instanceof ArrayBuffer && e.byteOffset === 0 && e.byteLength === e.buffer.byteLength ? e.buffer : e.slice().buffer;
}
var Rd = class e {
	static {
		this.ToRadioUuid = "f75c76d2-129e-4dad-a1dd-7866124401e7";
	}
	static {
		this.FromRadioUuid = "2c55e69e-4993-11ed-b878-0242ac120002";
	}
	static {
		this.FromNumUuid = "ed9da18c-a800-4f66-a670-aa7547e34453";
	}
	static {
		this.ServiceUuid = "6ba1b218-15a8-461f-9fa8-5dcae273eafd";
	}
	static async create() {
		let t = await navigator.bluetooth.requestDevice({ filters: [{ services: [e.ServiceUuid] }] });
		return await e.prepareConnection(t);
	}
	static async createFromDevice(t) {
		return await e.prepareConnection(t);
	}
	static async prepareConnection(t) {
		let n = await t.gatt?.connect();
		if (!n) throw Error("Failed to connect to GATT server");
		let r = await n.getPrimaryService(e.ServiceUuid), i = await r.getCharacteristic(e.ToRadioUuid), a = await r.getCharacteristic(e.FromRadioUuid), o = await r.getCharacteristic(e.FromNumUuid);
		if (!i || !a || !o) throw Error("Failed to find required characteristics");
		return new e(i, a, o, n);
	}
	constructor(e, t, n, r) {
		this.lastStatus = L.DeviceStatusEnum.DeviceDisconnected, this.closingByUser = !1, this.reading = !1, this.onGattDisconnected = () => {
			this.closingByUser || this.emitStatus(L.DeviceStatusEnum.DeviceDisconnected, "gatt-disconnected");
		}, this.onFromNumChanged = () => {
			this.readFromRadio();
		}, this.toRadioCharacteristic = e, this.fromRadioCharacteristic = t, this.fromNumCharacteristic = n, this.gattServer = r, this._fromDevice = new ReadableStream({ start: async (e) => {
			this.fromDeviceController = e, this.emitStatus(L.DeviceStatusEnum.DeviceConnecting), this.gattServer.device.addEventListener("gattserverdisconnected", this.onGattDisconnected);
			try {
				await this.fromNumCharacteristic.startNotifications(), this.fromNumCharacteristic.addEventListener("characteristicvaluechanged", this.onFromNumChanged), this.emitStatus(L.DeviceStatusEnum.DeviceConnected), this.readFromRadio();
			} catch {
				this.emitStatus(L.DeviceStatusEnum.DeviceDisconnected, "notify-failed"), this.gattServer.device.removeEventListener("gattserverdisconnected", this.onGattDisconnected);
			}
		} }), this._toDevice = new WritableStream({ write: async (e) => {
			try {
				let t = Ld(e);
				await this.toRadioCharacteristic.writeValue(t), this.readFromRadio();
			} catch (e) {
				throw this.emitStatus(L.DeviceStatusEnum.DeviceDisconnected, "write-error"), e;
			}
		} });
	}
	get toDevice() {
		return this._toDevice;
	}
	get fromDevice() {
		return this._fromDevice;
	}
	disconnect() {
		try {
			this.closingByUser = !0, this.emitStatus(L.DeviceStatusEnum.DeviceDisconnected, "user");
			try {
				this.fromNumCharacteristic.stopNotifications?.();
			} catch {}
			this.fromNumCharacteristic.removeEventListener("characteristicvaluechanged", this.onFromNumChanged), this.gattServer.device.removeEventListener("gattserverdisconnected", this.onGattDisconnected), this.gattServer.disconnect();
		} finally {
			this.closingByUser = !1;
		}
		return Promise.resolve();
	}
	async readFromRadio() {
		if (!this.reading) {
			this.reading = !0;
			try {
				let e = !0;
				for (; e && this.fromRadioCharacteristic;) {
					let t = await this.fromRadioCharacteristic.readValue();
					t.byteLength === 0 ? e = !1 : this.enqueue({
						type: "packet",
						data: new Uint8Array(t.buffer)
					});
				}
			} catch (e) {
				throw this.closingByUser || this.emitStatus(L.DeviceStatusEnum.DeviceDisconnected, "read-error"), e;
			} finally {
				this.reading = !1;
			}
		}
	}
	emitStatus(e, t) {
		e !== this.lastStatus && (this.lastStatus = e, this.fromDeviceController?.enqueue({
			type: "status",
			data: {
				status: e,
				reason: t
			}
		}));
	}
	enqueue(e) {
		this.fromDeviceController?.enqueue(e);
	}
}, zd = 3e3, Bd = 7e3, Vd = 4e3;
function Hd(e) {
	return e.buffer instanceof ArrayBuffer && e.byteOffset === 0 && e.byteLength === e.buffer.byteLength ? e.buffer : e.slice().buffer;
}
var Ud = class e {
	static async create(t, n) {
		let r = `${n ? "https" : "http"}://${t}`;
		return await fetch(`${r}/api/v1/toradio`, { method: "OPTIONS" }), new e(r);
	}
	constructor(e) {
		this.lastStatus = L.DeviceStatusEnum.DeviceDisconnected, this.closingByUser = !1, this.url = e, this.receiveBatchRequests = !1, this.fetchInterval = zd, this.fetching = !1, this._toDevice = new WritableStream({ write: async (e) => {
			try {
				await this.writeToRadio(e);
			} catch (e) {
				if (!this.closingByUser) {
					this.emitStatus(L.DeviceStatusEnum.DeviceDisconnected, this.isTimeoutOrAbort(e) ? "write-timeout" : "write-error");
					return;
				}
				throw e;
			}
		} }), this._fromDevice = new ReadableStream({
			start: (e) => {
				this.fromDeviceController = e, this.emitStatus(L.DeviceStatusEnum.DeviceConnecting), this.safePoll(), this.interval = setInterval(() => void this.safePoll(), this.fetchInterval);
			},
			cancel: () => {
				this.interval && clearInterval(this.interval), this.interval = void 0;
			}
		});
	}
	async readFromRadio() {
		let e = /* @__PURE__ */ new ArrayBuffer(1);
		for (; e.byteLength > 0;) {
			let t = new AbortController();
			this.inflightReadController = t;
			let n = AbortSignal.any([t.signal, AbortSignal.timeout(Bd)]);
			try {
				let t = await fetch(`${this.url}/api/v1/fromradio?all=${this.receiveBatchRequests ? "true" : "false"}`, {
					method: "GET",
					headers: { Accept: "application/x-protobuf" },
					signal: n
				});
				if (!t.ok) throw Error(`fromradio ${t.status} ${t.statusText}`);
				this.emitStatus(L.DeviceStatusEnum.DeviceConnected), e = await t.arrayBuffer(), e.byteLength > 0 && this.fromDeviceController?.enqueue({
					type: "packet",
					data: new Uint8Array(e)
				});
			} finally {
				this.inflightReadController = void 0;
			}
		}
	}
	async writeToRadio(e) {
		try {
			let t = await fetch(`${this.url}/api/v1/toradio`, {
				method: "PUT",
				headers: { "Content-Type": "application/x-protobuf" },
				body: Hd(e),
				signal: AbortSignal.timeout(Vd)
			});
			if (!t.ok) throw Error(`toradio ${t.status} ${t.statusText}`);
		} catch (e) {
			if (!this.closingByUser) {
				this.emitStatus(L.DeviceStatusEnum.DeviceDisconnected, this.isTimeoutOrAbort(e) ? "write-timeout" : "write-error");
				return;
			}
			throw e;
		}
	}
	get toDevice() {
		return this._toDevice;
	}
	get fromDevice() {
		return this._fromDevice;
	}
	disconnect() {
		this.closingByUser = !0, this.interval && clearInterval(this.interval), this.interval = void 0, this.fetching = !1;
		try {
			this.inflightReadController?.abort();
		} catch {}
		return this.inflightReadController = void 0, this.emitStatus(L.DeviceStatusEnum.DeviceDisconnected, "user"), Promise.resolve();
	}
	emitStatus(e, t) {
		e !== this.lastStatus && (this.lastStatus = e, this.fromDeviceController?.enqueue({
			type: "status",
			data: {
				status: e,
				reason: t
			}
		}));
	}
	isTimeoutOrAbort(e) {
		return e instanceof DOMException && (e.name === "AbortError" || e.name === "TimeoutError") || e instanceof Error && (e.name === "AbortError" || e.name === "TimeoutError");
	}
	async safePoll() {
		if (!this.fetching) {
			this.fetching = !0;
			try {
				await this.readFromRadio();
			} catch (e) {
				this.closingByUser || this.emitStatus(L.DeviceStatusEnum.DeviceDisconnected, this.isTimeoutOrAbort(e) ? "read-timeout" : "read-error");
			} finally {
				this.fetching = !1;
			}
		}
	}
}, B = Symbol("NOT_RESOLVED");
function V(e, t) {
	return {
		tagName: e,
		nodeKind: "scalar",
		implicit: t.implicit ?? !1,
		matchByTagPrefix: t.matchByTagPrefix ?? !1,
		implicitFirstChars: t.implicitFirstChars ?? null,
		resolve: t.resolve,
		identify: t.identify,
		represent: t.represent ?? ((e) => String(e)),
		representTagName: t.representTagName ?? (() => e)
	};
}
function Wd(e, t) {
	let n = t.finalize === void 0;
	return {
		tagName: e,
		nodeKind: "sequence",
		implicit: !1,
		matchByTagPrefix: t.matchByTagPrefix ?? !1,
		create: t.create,
		addItem: t.addItem,
		finalize: t.finalize ?? ((e) => e),
		carrierIsResult: n,
		identify: t.identify,
		represent: t.represent ?? ((e) => e),
		representTagName: t.representTagName ?? (() => e)
	};
}
function Gd(e, t) {
	let n = t.finalize === void 0;
	return {
		tagName: e,
		nodeKind: "mapping",
		implicit: !1,
		matchByTagPrefix: t.matchByTagPrefix ?? !1,
		create: t.create,
		addPair: t.addPair,
		has: t.has,
		keys: t.keys,
		get: t.get,
		finalize: t.finalize ?? ((e) => e),
		carrierIsResult: n,
		identify: t.identify,
		represent: t.represent ?? ((e) => e),
		representTagName: t.representTagName ?? (() => e)
	};
}
var Kd = V("tag:yaml.org,2002:str", {
	resolve: (e) => e,
	identify: (e) => typeof e == "string"
}), qd = [
	"",
	"~",
	"null",
	"Null",
	"NULL"
], Jd = V("tag:yaml.org,2002:null", {
	implicit: !0,
	implicitFirstChars: [
		"",
		"~",
		"n",
		"N"
	],
	resolve: (e) => qd.indexOf(e) === -1 ? B : null,
	identify: (e) => e === null,
	represent: () => "null"
}), Yd = V("tag:yaml.org,2002:null", {
	implicit: !0,
	implicitFirstChars: ["n"],
	resolve: (e, t) => e === "null" || t && e === "" ? null : B,
	identify: (e) => e === null,
	represent: () => "null"
}), Xd = [
	"",
	"~",
	"null",
	"Null",
	"NULL"
], Zd = V("tag:yaml.org,2002:null", {
	implicit: !0,
	implicitFirstChars: [
		"",
		"~",
		"n",
		"N"
	],
	resolve: (e) => Xd.indexOf(e) === -1 ? B : null,
	identify: (e) => e === null,
	represent: () => "null"
}), Qd = [
	"true",
	"True",
	"TRUE"
], $d = [
	"false",
	"False",
	"FALSE"
], ef = V("tag:yaml.org,2002:bool", {
	implicit: !0,
	implicitFirstChars: [
		"t",
		"T",
		"f",
		"F"
	],
	resolve: (e) => Qd.indexOf(e) !== -1 || $d.indexOf(e) === -1 && B,
	identify: (e) => Object.prototype.toString.call(e) === "[object Boolean]",
	represent: (e) => e ? "true" : "false"
}), tf = ["true"], nf = ["false"], rf = V("tag:yaml.org,2002:bool", {
	implicit: !0,
	implicitFirstChars: ["t", "f"],
	resolve: (e) => tf.indexOf(e) !== -1 || nf.indexOf(e) === -1 && B,
	identify: (e) => Object.prototype.toString.call(e) === "[object Boolean]",
	represent: (e) => e ? "true" : "false"
}), af = [
	"true",
	"True",
	"TRUE",
	"y",
	"Y",
	"yes",
	"Yes",
	"YES",
	"on",
	"On",
	"ON"
], of = [
	"false",
	"False",
	"FALSE",
	"n",
	"N",
	"no",
	"No",
	"NO",
	"off",
	"Off",
	"OFF"
], sf = V("tag:yaml.org,2002:bool", {
	implicit: !0,
	implicitFirstChars: [
		"y",
		"Y",
		"n",
		"N",
		"t",
		"T",
		"f",
		"F",
		"o",
		"O"
	],
	resolve: (e) => af.indexOf(e) !== -1 || of.indexOf(e) === -1 && B,
	identify: (e) => Object.prototype.toString.call(e) === "[object Boolean]",
	represent: (e) => e ? "true" : "false"
}), cf = /* @__PURE__ */ RegExp("^(?:0o[0-7]+|0x[0-9a-fA-F]+|[-+]?[0-9]+)$"), lf = /* @__PURE__ */ RegExp("^(?:[-+]?0b[0-1]+|[-+]?0o[0-7]+|[-+]?0x[0-9a-fA-F]+|[-+]?[0-9]+)$");
function uf(e) {
	let t = e, n = 1;
	return (t[0] === "-" || t[0] === "+") && (t[0] === "-" && (n = -1), t = t.slice(1)), t.startsWith("0b") ? n * parseInt(t.slice(2), 2) : t.startsWith("0o") ? n * parseInt(t.slice(2), 8) : t.startsWith("0x") ? n * parseInt(t.slice(2), 16) : n * parseInt(t, 10);
}
function df(e, t) {
	if (t) {
		if (!lf.test(e)) return B;
	} else if (!cf.test(e)) return B;
	let n = uf(e);
	return Number.isFinite(n) ? n : B;
}
var ff = V("tag:yaml.org,2002:int", {
	implicit: !0,
	implicitFirstChars: [
		"-",
		"+",
		..."0123456789"
	],
	resolve: df,
	identify: (e) => Number.isInteger(e) && !Object.is(e, -0) && e.toString(10).indexOf("e") < 0,
	represent: (e) => e.toString(10)
}), pf = /* @__PURE__ */ RegExp("^-?(?:0|[1-9][0-9]*)$"), mf = /* @__PURE__ */ RegExp("^(?:[-+]?0b[0-1]+|[-+]?0o[0-7]+|[-+]?0x[0-9a-fA-F]+|[-+]?[0-9]+)$");
function hf(e) {
	let t = e, n = 1;
	return (t[0] === "-" || t[0] === "+") && (t[0] === "-" && (n = -1), t = t.slice(1)), t.startsWith("0b") ? n * parseInt(t.slice(2), 2) : t.startsWith("0o") ? n * parseInt(t.slice(2), 8) : t.startsWith("0x") ? n * parseInt(t.slice(2), 16) : n * parseInt(t, 10);
}
function gf(e, t) {
	if (t) {
		if (!mf.test(e)) return B;
	} else if (!pf.test(e)) return B;
	let n = hf(e);
	return Number.isFinite(n) ? n : B;
}
var _f = V("tag:yaml.org,2002:int", {
	implicit: !0,
	implicitFirstChars: ["-", ..."0123456789"],
	resolve: gf,
	identify: (e) => Number.isInteger(e) && !Object.is(e, -0) && e.toString(10).indexOf("e") < 0,
	represent: (e) => e.toString(10)
}), vf = /* @__PURE__ */ RegExp("^(?:[-+]?0b[0-1_]+|[-+]?0[0-7_]+|[-+]?0x[0-9a-fA-F_]+|[-+]?[0-9][0-9_]*(?::[0-5]?[0-9])+|[-+]?(?:0|[1-9][0-9_]*))$");
function yf(e) {
	let t = e.replace(/_/g, ""), n = 1;
	if ((t[0] === "-" || t[0] === "+") && (t[0] === "-" && (n = -1), t = t.slice(1)), t.startsWith("0b")) return n * parseInt(t.slice(2), 2);
	if (t.startsWith("0x")) return n * parseInt(t.slice(2), 16);
	if (t.includes(":")) {
		let e = 0;
		for (let n of t.split(":")) e = e * 60 + Number(n);
		return n * e;
	}
	return t !== "0" && t[0] === "0" ? n * parseInt(t, 8) : n * parseInt(t, 10);
}
function bf(e) {
	if (!vf.test(e)) return B;
	let t = yf(e);
	return Number.isFinite(t) ? t : B;
}
var xf = V("tag:yaml.org,2002:int", {
	implicit: !0,
	implicitFirstChars: [
		"-",
		"+",
		..."0123456789"
	],
	resolve: bf,
	identify: (e) => Number.isInteger(e) && !Object.is(e, -0) && e.toString(10).indexOf("e") < 0,
	represent: (e) => e.toString(10)
}), Sf = /* @__PURE__ */ RegExp("^(?:[-+]?[0-9]+(?:\\.[0-9]*)?(?:[eE][-+]?[0-9]+)?|[-+]?\\.[0-9]+(?:[eE][-+]?[0-9]+)?|[-+]?\\.(?:inf|Inf|INF)|\\.(?:nan|NaN|NAN))$"), Cf = /* @__PURE__ */ RegExp("^(?:[-+]?\\.(?:inf|Inf|INF)|\\.(?:nan|NaN|NAN))$");
function wf(e) {
	if (!Sf.test(e)) return B;
	let t = e.toLowerCase(), n = t[0] === "-" ? -1 : 1;
	if ("+-".includes(t[0]) && (t = t.slice(1)), t === ".inf") return n === 1 ? Infinity : -Infinity;
	if (t === ".nan") return NaN;
	let r = n * parseFloat(t);
	return Number.isFinite(r) || Cf.test(e) ? r : B;
}
function Tf(e) {
	if (isNaN(e)) return ".nan";
	if (e === Infinity) return ".inf";
	if (e === -Infinity) return "-.inf";
	if (Object.is(e, -0)) return "-0.0";
	let t = e.toString(10);
	return /^[-+]?[0-9]+e/.test(t) ? t.replace("e", ".e") : t;
}
var Ef = V("tag:yaml.org,2002:float", {
	implicit: !0,
	implicitFirstChars: [
		"-",
		"+",
		".",
		..."0123456789"
	],
	resolve: wf,
	identify: (e) => typeof e == "number" && (!Number.isInteger(e) || Object.is(e, -0) || e.toString(10).indexOf("e") >= 0),
	represent: Tf
}), Df = /* @__PURE__ */ RegExp("^-?(?:0|[1-9][0-9]*)(?:\\.[0-9]*)?(?:[eE][-+]?[0-9]+)?$"), Of = /* @__PURE__ */ RegExp("^(?:[-+]?[0-9]+(?:\\.[0-9]*)?(?:[eE][-+]?[0-9]+)?|[-+]?\\.[0-9]+(?:[eE][-+]?[0-9]+)?|[-+]?\\.(?:inf|Inf|INF)|\\.(?:nan|NaN|NAN))$");
function kf(e, t) {
	if (t) {
		if (!Of.test(e)) return B;
		let t = e.toLowerCase(), n = t[0] === "-" ? -1 : 1;
		if ("+-".includes(t[0]) && (t = t.slice(1)), t === ".inf") return n === 1 ? Infinity : -Infinity;
		if (t === ".nan") return NaN;
		let r = n * parseFloat(t);
		return Number.isFinite(r) ? r : B;
	}
	if (!Df.test(e)) return B;
	let n = Number(e);
	return Number.isFinite(n) ? n : B;
}
function Af(e) {
	if (isNaN(e)) return ".nan";
	if (e === Infinity) return ".inf";
	if (e === -Infinity) return "-.inf";
	if (Object.is(e, -0)) return "-0.0";
	let t = e.toString(10);
	return /^[-+]?[0-9]+e/.test(t) ? t.replace("e", ".e") : t;
}
var jf = V("tag:yaml.org,2002:float", {
	implicit: !0,
	implicitFirstChars: ["-", ..."0123456789"],
	resolve: kf,
	identify: (e) => typeof e == "number" && (!Number.isInteger(e) || Object.is(e, -0) || e.toString(10).indexOf("e") >= 0),
	represent: Af
}), Mf = /* @__PURE__ */ RegExp("^(?:[-+]?(?:(?:[0-9][0-9_]*)?\\.[0-9_]*)(?:[eE][-+][0-9]+)?|[-+]?[0-9][0-9_]*(?::[0-5]?[0-9])+\\.[0-9_]*|[-+]?\\.(?:inf|Inf|INF)|\\.(?:nan|NaN|NAN))$"), Nf = /* @__PURE__ */ RegExp("^(?:[-+]?\\.(?:inf|Inf|INF)|\\.(?:nan|NaN|NAN))$");
function Pf(e) {
	if (!Mf.test(e)) return B;
	let t = e.toLowerCase().replace(/_/g, ""), n = t[0] === "-" ? -1 : 1;
	if ("+-".includes(t[0]) && (t = t.slice(1)), t === ".inf") return n === 1 ? Infinity : -Infinity;
	if (t === ".nan") return NaN;
	let r = 0;
	if (t.includes(":")) {
		for (let e of t.split(":")) r = r * 60 + Number(e);
		r *= n;
	} else r = n * parseFloat(t);
	return Number.isFinite(r) || Nf.test(e) ? r : B;
}
function Ff(e) {
	if (isNaN(e)) return ".nan";
	if (e === Infinity) return ".inf";
	if (e === -Infinity) return "-.inf";
	if (Object.is(e, -0)) return "-0.0";
	let t = e.toString(10);
	return /^[-+]?[0-9]+e/.test(t) ? t.replace("e", ".e") : t;
}
var If = V("tag:yaml.org,2002:float", {
	implicit: !0,
	implicitFirstChars: [
		"-",
		"+",
		".",
		..."0123456789"
	],
	resolve: Pf,
	identify: (e) => typeof e == "number" && (!Number.isInteger(e) || Object.is(e, -0) || e.toString(10).indexOf("e") >= 0),
	represent: Ff
}), Lf = V("tag:yaml.org,2002:merge", {
	implicit: !0,
	implicitFirstChars: ["<"],
	resolve: (e, t) => e === "<<" || t && e === "" ? "<<" : B,
	identify: () => !1
}), Rf = /^[A-Za-z0-9+/]*={0,2}$/;
function zf(e) {
	let t = e.replace(/\s/g, "");
	if (t.length % 4 != 0 || !Rf.test(t)) return B;
	let n = atob(t), r = new Uint8Array(n.length);
	for (let e = 0; e < n.length; e++) r[e] = n.charCodeAt(e);
	return r;
}
function Bf(e) {
	let t = "";
	for (let n = 0; n < e.length; n++) t += String.fromCharCode(e[n]);
	return btoa(t);
}
var Vf = V("tag:yaml.org,2002:binary", {
	resolve: zf,
	identify: (e) => Object.prototype.toString.call(e) === "[object Uint8Array]",
	represent: Bf
}), Hf = /* @__PURE__ */ RegExp("^([0-9][0-9][0-9][0-9])-([0-9][0-9])-([0-9][0-9])$"), Uf = /* @__PURE__ */ RegExp("^([0-9][0-9][0-9][0-9])-([0-9][0-9]?)-([0-9][0-9]?)(?:[Tt]|[ \\t]+)([0-9][0-9]?):([0-9][0-9]):([0-9][0-9])(?:\\.([0-9]*))?(?:[ \\t]*(Z|([-+])([0-9][0-9]?)(?::([0-9][0-9]))?))?$");
function Wf(e, t, n, r = 0, i = 0, a = 0, o = 0) {
	let s = new Date(Date.UTC(e, t, n, r, i, a, o));
	return s.setUTCFullYear(e, t, n), s;
}
function Gf(e) {
	let t = Hf.exec(e);
	if (t === null && (t = Uf.exec(e)), t === null) return B;
	let n = +t[1], r = t[2] - 1, i = +t[3];
	if (!t[4]) {
		let e = Wf(n, r, i);
		return e.getUTCFullYear() !== n || e.getUTCMonth() !== r || e.getUTCDate() !== i ? B : e;
	}
	let a = +t[4], o = +t[5], s = +t[6], c = 0;
	if (a > 23 || o > 59 || s > 59) return B;
	if (t[7]) {
		let e = t[7].slice(0, 3);
		for (; e.length < 3;) e += "0";
		c = +e;
	}
	let l = Wf(n, r, i, a, o, s, c);
	if (l.getUTCFullYear() !== n || l.getUTCMonth() !== r || l.getUTCDate() !== i) return B;
	if (t[9]) {
		let e = +t[10], n = +(t[11] || 0);
		if (e > 23 || n > 59) return B;
		let r = (e * 60 + n) * 6e4;
		l.setTime(l.getTime() - (t[9] === "-" ? -r : r));
	}
	return l;
}
var Kf = V("tag:yaml.org,2002:timestamp", {
	implicit: !0,
	implicitFirstChars: [..."0123456789"],
	resolve: Gf,
	identify: (e) => e instanceof Date,
	represent: (e) => e.toISOString()
}), qf = Wd("tag:yaml.org,2002:seq", {
	create: () => [],
	addItem: (e, t) => {
		e.push(t);
	},
	identify: Array.isArray
});
function Jf(e) {
	if (typeof e != "object" || !e || Array.isArray(e)) return !1;
	let t = Object.getPrototypeOf(e);
	return t === null || t === Object.prototype;
}
function Yf(e, t) {
	let n = {};
	for (let r of t) e[r] !== void 0 && (n[r] = e[r]);
	return n;
}
var Xf = Wd("tag:yaml.org,2002:omap", {
	create: () => ({
		list: [],
		seen: /* @__PURE__ */ new Set()
	}),
	addItem: (e, t) => {
		let n;
		if (t instanceof Map) {
			if (t.size !== 1) return "cannot resolve an ordered map item";
			n = t.keys().next().value;
		} else if (Jf(t)) {
			let e = Object.keys(t);
			if (e.length !== 1) return "cannot resolve an ordered map item";
			n = e[0];
		} else return "cannot resolve an ordered map item";
		return e.seen.has(n) ? "duplicate key in ordered map" : (e.seen.add(n), e.list.push(t), "");
	},
	finalize: (e) => e.list,
	identify: () => !1
}), Zf = Wd("tag:yaml.org,2002:pairs", {
	create: () => [],
	addItem: (e, t) => {
		if (t instanceof Map) return t.size === 1 ? (e.push(t.entries().next().value), "") : "cannot resolve a pairs item";
		if (Object.prototype.toString.call(t) !== "[object Object]") return "cannot resolve a pairs item";
		let n = t, r = Object.keys(n);
		return r.length === 1 ? (e.push([r[0], n[r[0]]]), "") : "cannot resolve a pairs item";
	},
	identify: () => !1
}), Qf = Gd("tag:yaml.org,2002:map", {
	create: () => ({}),
	identify: Jf,
	represent: (e) => {
		let t = /* @__PURE__ */ new Map();
		for (let n of Object.keys(e)) t.set(n, e[n]);
		return t;
	},
	addPair: (e, t, n) => {
		if (typeof t == "object" && t) return "object-based map does not support complex keys";
		let r = String(t);
		return r === "__proto__" ? Object.defineProperty(e, r, {
			value: n,
			enumerable: !0,
			configurable: !0,
			writable: !0
		}) : e[r] = n, "";
	},
	has: (e, t) => typeof t == "object" && t ? !1 : Object.prototype.hasOwnProperty.call(e, String(t)),
	keys: (e) => Object.keys(e),
	get: (e, t) => {
		let n = String(t);
		return Object.prototype.hasOwnProperty.call(e, n) ? e[n] : null;
	}
}), $f = Gd("tag:yaml.org,2002:set", {
	create: () => /* @__PURE__ */ new Set(),
	identify: (e) => e instanceof Set,
	represent: (e) => {
		let t = /* @__PURE__ */ new Map();
		for (let n of e) t.set(n, null);
		return t;
	},
	addPair: (e, t, n) => n === null ? (e.add(t), "") : "cannot resolve a set item",
	has: (e, t) => e.has(t),
	keys: (e) => e.keys(),
	get: () => null
});
function ep() {
	return {
		scalar: Object.create(null),
		sequence: Object.create(null),
		mapping: Object.create(null)
	};
}
function tp() {
	return {
		scalar: [],
		sequence: [],
		mapping: []
	};
}
function np(e) {
	let t = [];
	for (let n of e) {
		let e = t.length;
		for (let r = 0; r < t.length; r++) {
			let i = t[r];
			if (i.nodeKind === n.nodeKind && i.tagName === n.tagName && i.matchByTagPrefix === n.matchByTagPrefix) {
				e = r;
				break;
			}
		}
		t[e] = n;
	}
	return t;
}
var rp = class e {
	tags;
	implicitScalarTags;
	implicitScalarByFirstChar;
	implicitScalarAnyFirstChar;
	defaultScalarTag;
	defaultSequenceTag;
	defaultMappingTag;
	exact;
	prefix;
	constructor(e) {
		let t = np(e), n = [], r = ep(), i = tp();
		for (let e of t) {
			if (e.nodeKind === "scalar" && e.implicit) {
				if (e.matchByTagPrefix) throw Error("Implicit scalar tags cannot match by tag prefix");
				n.push(e);
			}
			switch (e.nodeKind) {
				case "scalar":
					e.matchByTagPrefix ? i.scalar.push(e) : r.scalar[e.tagName] = e;
					break;
				case "sequence":
					e.matchByTagPrefix ? i.sequence.push(e) : r.sequence[e.tagName] = e;
					break;
				case "mapping": e.matchByTagPrefix ? i.mapping.push(e) : r.mapping[e.tagName] = e;
			}
		}
		let a = n.filter((e) => e.implicitFirstChars === null), o = /* @__PURE__ */ new Set();
		for (let e of n) if (e.implicitFirstChars !== null) for (let t of e.implicitFirstChars) o.add(t);
		let s = /* @__PURE__ */ new Map();
		for (let e of o) s.set(e, n.filter((t) => t.implicitFirstChars === null || t.implicitFirstChars.indexOf(e) !== -1));
		let c = r.scalar["tag:yaml.org,2002:str"];
		if (!c) throw Error("schema does not define the default scalar tag (tag:yaml.org,2002:str)");
		this.tags = t, this.implicitScalarTags = n, this.implicitScalarByFirstChar = s, this.implicitScalarAnyFirstChar = a, this.defaultScalarTag = c, this.defaultSequenceTag = r.sequence["tag:yaml.org,2002:seq"], this.defaultMappingTag = r.mapping["tag:yaml.org,2002:map"], this.exact = r, this.prefix = i;
	}
	lookupScalarTag(e) {
		let t = this.exact.scalar[e];
		if (t) return t;
		for (let t of this.prefix.scalar) if (e.startsWith(t.tagName)) return t;
	}
	lookupSequenceTag(e) {
		let t = this.exact.sequence[e];
		if (t) return t;
		for (let t of this.prefix.sequence) if (e.startsWith(t.tagName)) return t;
	}
	lookupMappingTag(e) {
		let t = this.exact.mapping[e];
		if (t) return t;
		for (let t of this.prefix.mapping) if (e.startsWith(t.tagName)) return t;
	}
	resolveImplicitScalarTag(e) {
		let t = this.implicitScalarByFirstChar.get(e.charAt(0)) ?? this.implicitScalarAnyFirstChar;
		for (let n of t) {
			let t = n.resolve(e, !1, n.tagName);
			if (t !== B) return {
				value: t,
				tag: n
			};
		}
		let n = this.defaultScalarTag;
		return {
			value: n.resolve(e, !1, n.tagName),
			tag: n
		};
	}
	withTags(...t) {
		let n = [];
		for (let e of t) n = n.concat(e);
		return new e([...this.tags, ...n]);
	}
}, ip = new rp([
	Kd,
	qf,
	Qf
]);
new rp([
	...ip.tags,
	Yd,
	rf,
	_f,
	jf
]);
var ap = new rp([
	...ip.tags,
	Jd,
	ef,
	ff,
	Ef
]), op = new rp([
	...ip.tags,
	Zd,
	sf,
	xf,
	If,
	Kf,
	Lf,
	Vf,
	Xf,
	Zf,
	$f
]).withTags({
	...xf,
	resolve: (e, t, n) => {
		let r = xf.resolve(e, t, n);
		return r === B ? ff.resolve(e, t, n) : r;
	}
}, {
	...If,
	resolve: (e, t, n) => {
		let r = If.resolve(e, t, n);
		return r === B ? Ef.resolve(e, t, n) : r;
	}
});
Gd("tag:yaml.org,2002:map", {
	create: () => /* @__PURE__ */ new Map(),
	addPair: (e, t, n) => (e.set(t, n), ""),
	has: (e, t) => e.has(t),
	keys: (e) => e.keys(),
	get: (e, t) => e.get(t),
	identify: (e) => e instanceof Map || Jf(e),
	represent: (e) => {
		if (e instanceof Map) return e;
		let t = /* @__PURE__ */ new Map(), n = e;
		for (let e of Object.keys(n)) t.set(e, n[e]);
		return t;
	}
});
function sp(e) {
	if (Array.isArray(e)) {
		let t = Array.prototype.slice.call(e);
		for (let e = 0; e < t.length; e++) {
			if (Array.isArray(t[e])) return null;
			typeof t[e] == "object" && Object.prototype.toString.call(t[e]) === "[object Object]" && (t[e] = "[object Object]");
		}
		return String(t);
	}
	return typeof e == "object" && Object.prototype.toString.call(e) === "[object Object]" ? "[object Object]" : String(e);
}
Gd("tag:yaml.org,2002:map", {
	create: () => ({}),
	identify: Jf,
	represent: (e) => {
		let t = /* @__PURE__ */ new Map();
		for (let n of Object.keys(e)) t.set(n, e[n]);
		return t;
	},
	addPair: (e, t, n) => {
		let r = sp(t);
		return r === null ? "nested arrays are not supported inside keys" : (r === "__proto__" ? Object.defineProperty(e, r, {
			value: n,
			enumerable: !0,
			configurable: !0,
			writable: !0
		}) : e[r] = n, "");
	},
	has: (e, t) => {
		let n = sp(t);
		return n !== null && Object.prototype.hasOwnProperty.call(e, n);
	},
	keys: (e) => Object.keys(e),
	get: (e, t) => {
		let n = String(t);
		return Object.prototype.hasOwnProperty.call(e, n) ? e[n] : null;
	}
});
var cp = {
	maxLength: 79,
	indent: 1,
	linesBefore: 3,
	linesAfter: 2
};
function lp(e, t, n, r, i) {
	let a = "", o = "", s = Math.floor(i / 2) - 1;
	return r - t > s && (a = " ... ", t = r - s + a.length), n - r > s && (o = " ...", n = r + s - o.length), {
		str: a + e.slice(t, n).replace(/\t/g, "→") + o,
		pos: r - t + a.length
	};
}
function up(e, t) {
	return " ".repeat(Math.max(t - e.length, 0)) + e;
}
function dp(e, t) {
	if (!e.buffer) return null;
	let n = {
		...cp,
		...t
	}, r = /\r?\n|\r|\0/g, i = [0], a = [], o, s = -1;
	for (; o = r.exec(e.buffer);) a.push(o.index), i.push(o.index + o[0].length), e.position <= o.index && s < 0 && (s = i.length - 2);
	s < 0 && (s = i.length - 1);
	let c = "", l = Math.min(e.line + n.linesAfter, a.length).toString().length, u = n.maxLength - (n.indent + l + 3);
	for (let t = 1; t <= n.linesBefore && !(s - t < 0); t++) {
		let r = lp(e.buffer, i[s - t], a[s - t], e.position - (i[s] - i[s - t]), u);
		c = `${" ".repeat(n.indent)}${up((e.line - t + 1).toString(), l)} | ${r.str}\n${c}`;
	}
	let d = lp(e.buffer, i[s], a[s], e.position, u);
	c += `${" ".repeat(n.indent)}${up((e.line + 1).toString(), l)} | ${d.str}\n`, c += `${"-".repeat(n.indent + l + 3 + d.pos)}^\n`;
	for (let t = 1; t <= n.linesAfter && !(s + t >= a.length); t++) {
		let r = lp(e.buffer, i[s + t], a[s + t], e.position - (i[s] - i[s + t]), u);
		c += `${" ".repeat(n.indent)}${up((e.line + t + 1).toString(), l)} | ${r.str}\n`;
	}
	return c.replace(/\n$/, "");
}
function fp(e, t) {
	let n = "";
	return e.mark ? (e.mark.name && (n += `in "${e.mark.name}" `), n += `(${e.mark.line + 1}:${e.mark.column + 1})`, !t && e.mark.snippet && (n += `\n\n${e.mark.snippet}`), `${e.reason} ${n}`) : e.reason;
}
var pp = class e extends Error {
	reason;
	mark;
	constructor(e, t) {
		super(), this.name = "YAMLException", this.reason = e, this.mark = t, this.message = fp(this, !1), Error.captureStackTrace && Error.captureStackTrace(this, this.constructor);
	}
	toString(e) {
		return `${this.name}: ${fp(this, e)}`;
	}
	static throwAt(t, n, r, i = "") {
		let a = 0, o = 0;
		for (let e = 0; e < n; e++) {
			let n = t.charCodeAt(e);
			n === 10 ? (a++, o = e + 1) : n === 13 && (a++, t.charCodeAt(e + 1) === 10 && e++, o = e + 1);
		}
		let s = {
			name: i,
			buffer: t,
			position: n,
			line: a,
			column: n - o
		};
		throw s.snippet = dp(s), new e(r, s);
	}
}, H = {
	DOCUMENT: 1,
	SEQUENCE: 2,
	MAPPING: 3,
	SCALAR: 4,
	ALIAS: 5,
	POP: 6
}, U = {
	PLAIN: 1,
	SINGLE_QUOTED: 2,
	DOUBLE_QUOTED: 3,
	LITERAL_BLOCK: 4,
	FOLDED_BLOCK: 5
}, W = {
	BLOCK: 1,
	FLOW: 2
}, G = {
	CLIP: 1,
	STRIP: 2,
	KEEP: 3
}, mp = -1;
function hp(e) {
	switch (e) {
		case 48: return "\0";
		case 97: return "\x07";
		case 98: return "\b";
		case 116: return "	";
		case 9: return "	";
		case 110: return "\n";
		case 118: return "\v";
		case 102: return "\f";
		case 114: return "\r";
		case 101: return "\x1B";
		case 32: return " ";
		case 34: return "\"";
		case 47: return "/";
		case 92: return "\\";
		case 78: return "";
		case 95: return "\xA0";
		case 76: return "\u2028";
		case 80: return "\u2029";
		default: return "";
	}
}
var gp = Array(256), _p = Array(256);
for (let e = 0; e < 256; e++) gp[e] = +!!hp(e), _p[e] = hp(e);
function vp(e) {
	return e <= 65535 ? String.fromCharCode(e) : String.fromCharCode((e - 65536 >> 10) + 55296, (e - 65536 & 1023) + 56320);
}
function yp(e) {
	return e >= 48 && e <= 57 ? e - 48 : (e | 32) - 97 + 10;
}
function bp(e) {
	return e === 120 ? 2 : e === 117 ? 4 : 8;
}
function xp(e, t, n) {
	let r = 0;
	for (; t < n;) {
		let n = e.charCodeAt(t);
		if (n === 10) r++, t++;
		else if (n === 13) r++, t++, e.charCodeAt(t) === 10 && t++;
		else if (n === 32 || n === 9) t++;
		else break;
	}
	return {
		position: t,
		breaks: r
	};
}
function Sp(e) {
	return e === 1 ? " " : "\n".repeat(e - 1);
}
function Cp(e, t, n) {
	let r = "", i = t, a = t, o = t;
	for (; i < n;) {
		let t = e.charCodeAt(i);
		if (t === 10 || t === 13) {
			r += e.slice(a, o);
			let t = xp(e, i, n);
			r += Sp(t.breaks), i = a = o = t.position;
		} else i++, t !== 32 && t !== 9 && (o = i);
	}
	return r + e.slice(a, o);
}
function wp(e, t, n) {
	let r = "", i = t, a = t, o = t;
	for (; i < n;) {
		let t = e.charCodeAt(i);
		if (t === 39) r += e.slice(a, i) + "'", i += 2, a = o = i;
		else if (t === 10 || t === 13) {
			r += e.slice(a, o);
			let t = xp(e, i, n);
			r += Sp(t.breaks), i = a = o = t.position;
		} else i++, t !== 32 && t !== 9 && (o = i);
	}
	return r + e.slice(a, n);
}
function Tp(e, t, n) {
	let r = "", i = t, a = t, o = t;
	for (; i < n;) {
		let t = e.charCodeAt(i);
		if (t === 92) {
			r += e.slice(a, i), i++;
			let t = e.charCodeAt(i);
			if (t === 10 || t === 13) i = xp(e, i, n).position;
			else if (t < 256 && gp[t]) r += _p[t], i++;
			else {
				let n = bp(t), a = 0;
				for (; n > 0; n--) {
					i++;
					let t = yp(e.charCodeAt(i));
					a = (a << 4) + t;
				}
				r += vp(a), i++;
			}
			a = o = i;
		} else if (t === 10 || t === 13) {
			r += e.slice(a, o);
			let t = xp(e, i, n);
			r += Sp(t.breaks), i = a = o = t.position;
		} else i++, t !== 32 && t !== 9 && (o = i);
	}
	return r + e.slice(a, n);
}
function Ep(e, t, n, r, i, a) {
	let o = r < 0 ? 0 : r, s = e.slice(t, n).replace(/\r\n?/g, "\n"), c = s === "" ? [] : (s.endsWith("\n") ? s.slice(0, -1) : s).split("\n"), l = "", u = !1, d = 0, f = !1;
	for (let e of c) {
		let t = 0;
		for (; t < o && e.charCodeAt(t) === 32;) t++;
		if (r < 0 || t >= e.length) {
			d++;
			continue;
		}
		let n = e.slice(o), i = n.charCodeAt(0);
		a ? i === 32 || i === 9 ? (f = !0, l += "\n".repeat(u ? 1 + d : d)) : f ? (f = !1, l += "\n".repeat(d + 1)) : d === 0 ? u && (l += " ") : l += "\n".repeat(d) : l += "\n".repeat(u ? 1 + d : d), l += n, u = !0, d = 0;
	}
	return i === G.KEEP ? l += "\n".repeat(u ? 1 + d : d) : i !== G.STRIP && u && (l += "\n"), l;
}
function Dp(e, t) {
	if (t.valueStart === mp) return "";
	let { valueStart: n, valueEnd: r } = t;
	if (t.fast) return e.slice(n, r);
	switch (t.style) {
		case U.SINGLE_QUOTED: return wp(e, n, r);
		case U.DOUBLE_QUOTED: return Tp(e, n, r);
		case U.LITERAL_BLOCK: return Ep(e, n, r, t.indent, t.chomping, !1);
		case U.FOLDED_BLOCK: return Ep(e, n, r, t.indent, t.chomping, !0);
		default: return Cp(e, n, r);
	}
}
var Op = Object.assign(Object.create(null), {
	"!": "!",
	"!!": "tag:yaml.org,2002:"
});
function kp(e) {
	return encodeURI(e).replace(/!/g, "%21");
}
function Ap(e, t) {
	if (e.startsWith("!<") && e.endsWith(">")) return decodeURIComponent(e.slice(2, -1));
	let n = e.indexOf("!", 1), r = n === -1 ? "!" : e.slice(0, n + 1), i = t?.[r] ?? Op[r] ?? r;
	return decodeURIComponent(i) + decodeURIComponent(e.slice(r.length));
}
function jp(e) {
	let t = e;
	return t.charCodeAt(0) === 33 ? (t = t.slice(1), `!${kp(t)}`) : t.slice(0, 18) === "tag:yaml.org,2002:" ? `!!${kp(t.slice(18))}` : `!<${kp(t)}>`;
}
var Mp = -1, Np = "tag:yaml.org,2002:merge", Pp = {
	filename: "",
	schema: ap,
	json: !1,
	maxTotalMergeKeys: 1e4,
	maxAliases: -1
};
function Fp(e) {
	return "tagStart" in e && e.tagStart !== Mp ? e.tagStart : "anchorStart" in e && e.anchorStart !== Mp ? e.anchorStart : "valueStart" in e && e.valueStart !== Mp ? e.valueStart : "start" in e ? e.start : 0;
}
function K(e, t) {
	pp.throwAt(e.source, e.position, t, e.filename);
}
function Ip(e, t, n, r) {
	try {
		return n.finalize(r);
	} catch (n) {
		if (n instanceof pp) throw n;
		pp.throwAt(e.source, t, n instanceof Error ? n.message : String(n), e.filename);
	}
}
function Lp(e, t) {
	let n = Dp(e.source, t), r = t.tagStart === Mp ? "" : e.source.slice(t.tagStart, t.tagEnd), i = e.schema.defaultScalarTag;
	if (r !== "") {
		if (r === "!") return {
			value: n,
			tag: i
		};
		let t = Ap(r, e.tagHandlers), a = e.schema.lookupScalarTag(t);
		if (a) {
			let r = a.resolve(n, !0, t);
			return r === B && K(e, `cannot resolve a node with !<${t}> explicit tag`), {
				value: r,
				tag: a
			};
		}
		let o = e.schema.lookupMappingTag(t) ?? e.schema.lookupSequenceTag(t);
		if (o) {
			n !== "" && K(e, `cannot resolve a node with !<${t}> explicit tag`);
			let r = o.create(t);
			return {
				value: o.carrierIsResult ? r : Ip(e, e.position, o, r),
				tag: o
			};
		}
		K(e, `unknown scalar tag !<${t}>`);
	}
	return t.style === U.PLAIN ? e.schema.resolveImplicitScalarTag(n) : {
		value: i.resolve(n, !1, i.tagName),
		tag: i
	};
}
function Rp(e, t, n) {
	let r = t.tagStart === Mp ? "" : e.source.slice(t.tagStart, t.tagEnd);
	return r === "" || r === "!" ? n : Ap(r, e.tagHandlers);
}
function zp(e) {
	return e.nodeKind === "mapping";
}
function Bp(e) {
	e.totalMergeKeys++, e.maxTotalMergeKeys !== -1 && e.totalMergeKeys > e.maxTotalMergeKeys && K(e, `merge keys exceeded maxTotalMergeKeys (${e.maxTotalMergeKeys})`);
}
function Vp(e, t, n, r) {
	Bp(e);
	for (let i of r.keys(n)) {
		if (Bp(e), t.tag.has(t.value, i)) continue;
		let a = t.tag.addPair(t.value, i, r.get(n, i));
		a && K(e, a), t.overridable ??= /* @__PURE__ */ new Set(), t.overridable.add(i);
	}
}
function Hp(e, t, n, r) {
	if (e.position = t.keyPosition, zp(r)) Vp(e, t, n, r);
	else if (r.nodeKind === "sequence" && Array.isArray(n)) {
		n.length > 100 && K(e, "abnormal merge sequence size");
		for (let r of n) {
			let n = e.nodeTags.get(r);
			n || K(e, "cannot merge mappings; the provided source object is unacceptable"), Vp(e, t, r, n);
		}
	} else K(e, "cannot merge mappings; the provided source object is unacceptable");
}
function Up(e, t, n, r, i) {
	if (e.position = t.keyPosition, t.keyIsMerge) {
		Hp(e, t, r, i);
		return;
	}
	!e.json && t.tag.has(t.value, n) && !t.overridable?.has(n) && K(e, "duplicated mapping key");
	let a = t.tag.addPair(t.value, n, r);
	a && K(e, a), t.overridable?.delete(n);
}
function Wp(e, t, n) {
	let r = e.frames[e.frames.length - 1];
	if (r.kind === "document") r.value = t, r.hasValue = !0;
	else if (r.kind === "sequence") {
		zp(n) && e.nodeTags.set(t, n);
		let i = r.tag.addItem(r.value, t, r.index++);
		i && K(e, i);
	} else if (r.hasKey) {
		let i = r.key;
		r.key = void 0, r.hasKey = !1, Up(e, r, i, t, n);
	} else r.key = t, r.keyPosition = e.position, r.hasKey = !0, r.keyIsMerge = n.tagName === Np;
}
function Gp(e, t, n, r, i) {
	if (t.anchorStart !== Mp) {
		let a = {
			value: n,
			tag: r,
			isValueFinal: i
		};
		return e.anchors.set(e.source.slice(t.anchorStart, t.anchorEnd), a), a;
	}
	return null;
}
function Kp(e, t) {
	let n = {
		...Pp,
		...t,
		events: e,
		documents: [],
		eventIndex: 0,
		position: 0,
		frames: [],
		anchors: /* @__PURE__ */ new Map(),
		nodeTags: /* @__PURE__ */ new Map(),
		tagHandlers: Object.create(null),
		totalMergeKeys: 0,
		aliasCount: 0
	};
	for (; n.eventIndex < n.events.length;) {
		let e = n.events[n.eventIndex++];
		switch (n.position = Fp(e), e.type) {
			case H.DOCUMENT:
				n.anchors = /* @__PURE__ */ new Map(), n.nodeTags = /* @__PURE__ */ new Map(), n.aliasCount = 0, n.tagHandlers = Object.create(null);
				for (let t of e.directives) t.kind === "tag" && (n.tagHandlers[t.handle] = t.prefix);
				n.frames.push({
					kind: "document",
					position: n.position,
					value: void 0,
					hasValue: !1
				});
				break;
			case H.SCALAR: {
				let { value: t, tag: r } = Lp(n, e);
				Gp(n, e, t, r, !0), Wp(n, t, r);
				break;
			}
			case H.SEQUENCE: {
				let t = Rp(n, e, "tag:yaml.org,2002:seq"), r = n.schema.lookupSequenceTag(t);
				r || K(n, `unknown sequence tag !<${t}>`);
				let i = r.create(t), a = Gp(n, e, i, r, r.carrierIsResult);
				n.frames.push({
					kind: "sequence",
					position: n.position,
					value: i,
					tag: r,
					anchor: a,
					index: 0
				});
				break;
			}
			case H.MAPPING: {
				let t = Rp(n, e, "tag:yaml.org,2002:map"), r = n.schema.lookupMappingTag(t);
				r || K(n, `unknown mapping tag !<${t}>`);
				let i = r.create(t), a = Gp(n, e, i, r, r.carrierIsResult);
				n.frames.push({
					kind: "mapping",
					position: n.position,
					value: i,
					tag: r,
					anchor: a,
					key: void 0,
					keyPosition: n.position,
					hasKey: !1,
					keyIsMerge: !1,
					overridable: null
				});
				break;
			}
			case H.ALIAS: {
				n.maxAliases !== -1 && ++n.aliasCount > n.maxAliases && K(n, `aliases exceeded maxAliases (${n.maxAliases})`);
				let t = n.source.slice(e.anchorStart, e.anchorEnd), r = n.anchors.get(t);
				r || K(n, `unidentified alias "${t}"`), r.isValueFinal || K(n, `recursive alias "${t}" is not supported for tag ${r.tag.tagName} because it uses finalize()`), Wp(n, r.value, r.tag);
				break;
			}
			case H.POP: {
				let e = n.frames.pop();
				if (e.kind === "mapping" && e.hasKey && (n.position = e.keyPosition, K(n, "incomplete mapping pair in event stream")), e.kind === "document") n.documents.push(e.value);
				else {
					let t = e.tag.carrierIsResult ? e.value : Ip(n, e.position, e.tag, e.value);
					e.anchor && (e.anchor.value = t, e.anchor.isValueFinal = !0), Wp(n, t, e.tag);
				}
				break;
			}
		}
	}
	return n.documents;
}
var q = -1, qp = Object.prototype.hasOwnProperty, Jp = 1, Yp = 2, Xp = 3, Zp = 4, Qp = /[\x00-\x08\x0B\x0C\x0E-\x1F\x7F-\x84\x86-\x9F\uFFFE\uFFFF]|[\uD800-\uDBFF](?![\uDC00-\uDFFF])|(?:[^\uD800-\uDBFF]|^)[\uDC00-\uDFFF]/, $p = /[,\[\]{}]/, em = /^(?:!|!!|![0-9A-Za-z-]+!)$/, tm = String.raw`(?:%[0-9A-Fa-f]{2}|[0-9A-Za-z\-#;/?:@&=+$,_.!~*'()\[\]])`, nm = String.raw`(?:%[0-9A-Fa-f]{2}|[0-9A-Za-z\-#;/?:@&=+$.~*'()_])`, rm = RegExp(`^(?:${tm})*$`), im = RegExp(`^(?:${nm})+$`), am = RegExp(`^(?:!(?:${tm})*|${nm}(?:${tm})*)$`), om = {
	filename: "",
	maxDepth: 100
};
function sm(e, t, n) {
	e.events.push({
		type: H.DOCUMENT,
		explicitStart: t,
		explicitEnd: n,
		directives: e.directives
	});
}
function cm(e, t, n, r, i, a, o) {
	e.events.push({
		type: H.SEQUENCE,
		start: t,
		anchorStart: n,
		anchorEnd: r,
		tagStart: i,
		tagEnd: a,
		style: o
	});
}
function lm(e, t, n, r, i, a, o) {
	e.events.push({
		type: H.MAPPING,
		start: t,
		anchorStart: n,
		anchorEnd: r,
		tagStart: i,
		tagEnd: a,
		style: o
	});
}
function um(e, t) {
	e.events.splice(t.eventsLength, 0, {
		type: H.MAPPING,
		start: t.position,
		anchorStart: q,
		anchorEnd: q,
		tagStart: q,
		tagEnd: q,
		style: W.FLOW
	});
}
function dm(e, t, n, r, i, a, o, s, c = G.CLIP, l = -1, u = !1) {
	e.events.push({
		type: H.SCALAR,
		valueStart: t,
		valueEnd: n,
		anchorStart: r,
		anchorEnd: i,
		tagStart: a,
		tagEnd: o,
		style: s,
		chomping: c,
		indent: l,
		fast: u
	});
}
function fm(e, t, n) {
	e.events.push({
		type: H.ALIAS,
		anchorStart: t,
		anchorEnd: n
	});
}
function pm(e) {
	e.events.push({ type: H.POP });
}
function J(e) {
	dm(e, q, q, q, q, q, q, U.PLAIN);
}
function mm() {
	return {
		anchorStart: q,
		anchorEnd: q,
		tagStart: q,
		tagEnd: q
	};
}
function hm(e) {
	return {
		position: e.position,
		line: e.line,
		lineStart: e.lineStart,
		lineIndent: e.lineIndent,
		firstTabInLine: e.firstTabInLine,
		eventsLength: e.events.length
	};
}
function gm(e, t) {
	e.position = t.position, e.line = t.line, e.lineStart = t.lineStart, e.lineIndent = t.lineIndent, e.firstTabInLine = t.firstTabInLine, e.events.length = t.eventsLength;
}
function Y(e, t) {
	pp.throwAt(e.input.slice(0, e.length), e.position, t, e.filename);
}
function X(e) {
	return e === 10 || e === 13;
}
function _m(e) {
	return e === 9 || e === 32;
}
function vm(e) {
	return _m(e) || X(e);
}
function ym(e) {
	return e === 0 || vm(e);
}
function bm(e) {
	return e === 44 || e === 91 || e === 93 || e === 123 || e === 125;
}
function xm(e) {
	return e >= 48 && e <= 57 ? e - 48 : -1;
}
function Sm(e) {
	if (e >= 48 && e <= 57) return e - 48;
	let t = e | 32;
	return t >= 97 && t <= 102 ? t - 97 + 10 : -1;
}
function Cm(e) {
	return e === 120 ? 2 : e === 117 ? 4 : e === 85 ? 8 : 0;
}
function wm(e) {
	return e === 48 || e === 97 || e === 98 || e === 116 || e === 9 || e === 110 || e === 118 || e === 102 || e === 114 || e === 101 || e === 32 || e === 34 || e === 47 || e === 92 || e === 78 || e === 95 || e === 76 || e === 80;
}
function Tm(e) {
	e.input.charCodeAt(e.position) === 10 ? e.position++ : (e.position++, e.input.charCodeAt(e.position) === 10 && e.position++), e.line++, e.lineStart = e.position, e.lineIndent = 0, e.firstTabInLine = -1;
}
function Z(e, t) {
	let n = 0, r = e.input.charCodeAt(e.position), i = e.position === e.lineStart || vm(e.input.charCodeAt(e.position - 1));
	for (; r !== 0;) {
		for (; _m(r);) i = !0, r === 9 && e.firstTabInLine === -1 && (e.firstTabInLine = e.position), r = e.input.charCodeAt(++e.position);
		if (t && i && r === 35) do
			r = e.input.charCodeAt(++e.position);
		while (!X(r) && r !== 0);
		if (!X(r)) break;
		for (Tm(e), n++, i = !0, r = e.input.charCodeAt(e.position); r === 32;) e.lineIndent++, r = e.input.charCodeAt(++e.position);
	}
	return n;
}
function Em(e, t = e.position) {
	let n = e.input.charCodeAt(t);
	if ((n === 45 || n === 46) && n === e.input.charCodeAt(t + 1) && n === e.input.charCodeAt(t + 2)) {
		let n = e.input.charCodeAt(t + 3);
		return n === 0 || vm(n);
	}
	return !1;
}
function Dm(e) {
	e.position === e.lineStart && e.input.charCodeAt(e.position) === 65279 && (e.position++, e.lineStart = e.position);
}
function Om(e) {
	if (e.position !== e.lineStart) return !1;
	if (Em(e)) return !0;
	if (e.input.charCodeAt(e.position) !== 65279) return !1;
	let t = hm(e);
	Dm(e), Z(e, !0);
	let n = e.input.charCodeAt(e.position), r = e.position === e.lineStart && (n === 37 || n === 45 && Em(e));
	return gm(e, t), r;
}
function km(e) {
	let t = e.input.charCodeAt(e.position);
	for (; t !== 0 && !X(t);) t = e.input.charCodeAt(++e.position);
}
function Am(e, t, n) {
	Qp.test(e.input.slice(t, n)) && Y(e, "the stream contains non-printable characters");
}
function jm(e, t, n) {
	if (e.input.charCodeAt(e.position) !== 33) return !1;
	t.tagStart !== q && Y(e, "duplication of a tag property");
	let r = e.position, i = !1, a = !1, o = "!", s = e.input.charCodeAt(++e.position);
	s === 60 ? (i = !0, s = e.input.charCodeAt(++e.position)) : s === 33 && (a = !0, o = "!!", s = e.input.charCodeAt(++e.position));
	let c = e.position, l;
	if (i) {
		for (; s !== 0 && s !== 62;) s = e.input.charCodeAt(++e.position);
		s !== 62 && Y(e, "unexpected end of the stream within a verbatim tag"), l = e.input.slice(c, e.position), e.position++;
	} else {
		for (; s !== 0 && !vm(s) && !(n && bm(s));) s === 33 && (a ? Y(e, "tag suffix cannot contain exclamation marks") : (o = e.input.slice(c - 1, e.position + 1), em.test(o) || Y(e, "named tag handle cannot contain such characters"), a = !0, c = e.position + 1)), s = e.input.charCodeAt(++e.position);
		l = e.input.slice(c, e.position), $p.test(l) && Y(e, "tag suffix cannot contain flow indicator characters");
	}
	return l && !(i ? rm.test(l) : im.test(l)) && Y(e, `tag name cannot contain such characters: ${l}`), !i && o !== "!" && o !== "!!" && !qp.call(e.tagHandlers, o) && Y(e, `undeclared tag handle "${o}"`), t.tagStart = r, t.tagEnd = e.position, !0;
}
function Mm(e, t) {
	if (e.input.charCodeAt(e.position) !== 38) return !1;
	t.anchorStart !== q && Y(e, "duplication of an anchor property"), e.position++;
	let n = e.position;
	for (; e.input.charCodeAt(e.position) !== 0 && !vm(e.input.charCodeAt(e.position)) && !bm(e.input.charCodeAt(e.position));) e.position++;
	return e.position === n && Y(e, "name of an anchor node must contain at least one character"), t.anchorStart = n, t.anchorEnd = e.position, !0;
}
function Nm(e, t) {
	if (e.input.charCodeAt(e.position) !== 42) return !1;
	(t.anchorStart !== q || t.tagStart !== q) && Y(e, "alias node should not have any properties"), e.position++;
	let n = e.position;
	for (; e.input.charCodeAt(e.position) !== 0 && !vm(e.input.charCodeAt(e.position)) && !bm(e.input.charCodeAt(e.position));) e.position++;
	return e.position === n && Y(e, "name of an alias node must contain at least one character"), fm(e, n, e.position), !0;
}
function Pm(e, t) {
	Z(e, !1), e.lineIndent < t && Y(e, "deficient indentation");
}
function Fm(e, t, n) {
	if (e.input.charCodeAt(e.position) !== 39) return !1;
	e.position++;
	let r = e.position, i = !0;
	for (; e.input.charCodeAt(e.position) !== 0;) {
		let a = e.input.charCodeAt(e.position);
		if (a === 39) {
			if (e.input.charCodeAt(e.position + 1) === 39) {
				i = !1, e.position += 2;
				continue;
			}
			let t = e.position;
			return e.position++, dm(e, r, t, n.anchorStart, n.anchorEnd, n.tagStart, n.tagEnd, U.SINGLE_QUOTED, G.CLIP, -1, i), !0;
		}
		X(a) ? (i = !1, Pm(e, t)) : e.position === e.lineStart && Em(e) ? Y(e, "unexpected end of the document within a single quoted scalar") : a !== 9 && a < 32 ? Y(e, "expected valid JSON character") : e.position++;
	}
	Y(e, "unexpected end of the stream within a single quoted scalar");
}
function Im(e, t, n) {
	if (e.input.charCodeAt(e.position) !== 34) return !1;
	e.position++;
	let r = e.position, i = !0;
	for (; e.input.charCodeAt(e.position) !== 0;) {
		let a = e.input.charCodeAt(e.position);
		if (a === 34) {
			let t = e.position;
			return e.position++, dm(e, r, t, n.anchorStart, n.anchorEnd, n.tagStart, n.tagEnd, U.DOUBLE_QUOTED, G.CLIP, -1, i), !0;
		}
		if (a === 92) {
			i = !1;
			let n = e.input.charCodeAt(++e.position);
			if (X(n)) Pm(e, t);
			else if (wm(n)) e.position++;
			else {
				let t = Cm(n);
				for (t === 0 && Y(e, "unknown escape sequence"); t-- > 0;) e.position++, Sm(e.input.charCodeAt(e.position)) < 0 && Y(e, "expected hexadecimal character");
				e.position++;
			}
		} else X(a) ? (i = !1, Pm(e, t)) : e.position === e.lineStart && Em(e) ? Y(e, "unexpected end of the document within a double quoted scalar") : a !== 9 && a < 32 ? Y(e, "expected valid JSON character") : e.position++;
	}
	Y(e, "unexpected end of the stream within a double quoted scalar");
}
function Lm(e, t, n) {
	let r = e.input.charCodeAt(e.position), i = G.CLIP, a = -1, o = !1;
	if (r !== 124 && r !== 62) return !1;
	let s = r === 124 ? U.LITERAL_BLOCK : U.FOLDED_BLOCK;
	for (e.position++; e.input.charCodeAt(e.position) !== 0;) {
		let n = e.input.charCodeAt(e.position), r = xm(n);
		if (n === 43 || n === 45) i !== G.CLIP && Y(e, "repeat of a chomping mode identifier"), i = n === 43 ? G.KEEP : G.STRIP, e.position++;
		else if (r >= 0) r === 0 && Y(e, "bad explicit indentation width of a block scalar; it cannot be less than one"), o && Y(e, "repeat of an indentation width identifier"), a = t + r - 1, o = !0, e.position++;
		else break;
	}
	let c = !1;
	for (; _m(e.input.charCodeAt(e.position));) c = !0, e.position++;
	c && e.input.charCodeAt(e.position) === 35 && km(e), X(e.input.charCodeAt(e.position)) ? Tm(e) : e.input.charCodeAt(e.position) !== 0 && Y(e, "a line break is expected");
	let l = o ? a : -1, u = 0, d = e.position, f = e.position;
	for (; e.input.charCodeAt(e.position) !== 0;) {
		let n = e.position, r = 0;
		for (; e.input.charCodeAt(n + r) === 32;) r++;
		let i = e.input.charCodeAt(n + r);
		if (i === 0) {
			l >= 0 ? r > l && (f = n + r) : r > 0 && (f = n + r);
			break;
		}
		if (Om(e)) break;
		if (!o && l === -1 && X(i) && (u = Math.max(u, r)), !o && l === -1 && !X(i) && (i === 9 && r < t && (e.position = n + r, Y(e, "tab characters must not be used in indentation")), r >= t && r < u && (e.position = n + r, Y(e, "bad indentation of a mapping entry"))), l === -1 && i !== 0 && !X(i) && r < t) {
			e.lineIndent = r, e.position = n + r;
			break;
		}
		!o && i !== 0 && !X(i) && l === -1 && (l = r);
		let a = l === -1 ? t + 1 : l;
		if (i !== 0 && !X(i) && r < a) {
			e.lineIndent = r, e.position = n + r;
			break;
		}
		km(e), f = e.position, X(e.input.charCodeAt(e.position)) && (Tm(e), f = e.position);
	}
	return Am(e, d, f), dm(e, d, f, n.anchorStart, n.anchorEnd, n.tagStart, n.tagEnd, s, i, l), !0;
}
function Rm(e, t) {
	let n = e.input.charCodeAt(e.position), r = t === Jp;
	if (n === 0 || vm(n) || n === 35 || n === 38 || n === 42 || n === 33 || n === 124 || n === 62 || n === 39 || n === 34 || n === 37 || n === 64 || n === 96 || r && bm(n)) return !1;
	if (n === 63 || n === 45) {
		let t = e.input.charCodeAt(e.position + 1);
		if (ym(t) || r && bm(t)) return !1;
	}
	return !0;
}
function zm(e, t, n, r) {
	if (!Rm(e, n)) return !1;
	let i = e.position, a = e.position, o = e.input.charCodeAt(e.position), s = n === Jp, c = !1;
	for (; o !== 0 && !Om(e);) {
		if (o === 58) {
			let t = e.input.charCodeAt(e.position + 1);
			if (ym(t) || s && bm(t)) break;
		} else if (o === 35) {
			if (vm(e.input.charCodeAt(e.position - 1))) break;
		} else if (s && bm(o)) break;
		else if (X(o)) {
			let n = e.position, r = e.line, i = e.lineStart, a = e.lineIndent;
			if (Z(e, !1), e.lineIndent >= t) {
				c = !0, o = e.input.charCodeAt(e.position);
				continue;
			}
			e.position = n, e.line = r, e.lineStart = i, e.lineIndent = a;
			break;
		}
		_m(o) || (a = e.position + 1), o = e.input.charCodeAt(++e.position);
	}
	return a !== i && (Am(e, i, a), dm(e, i, a, r.anchorStart, r.anchorEnd, r.tagStart, r.tagEnd, U.PLAIN, G.CLIP, -1, !c), !0);
}
function Bm(e, t) {
	let n = e.line;
	Z(e, !0), (e.line > n && e.lineIndent < t || e.firstTabInLine !== -1 && e.lineIndent < t) && Y(e, "deficient indentation");
}
function Vm(e, t, n) {
	let r = e.input.charCodeAt(e.position), i = r === 123, a = e.position, o = !0;
	if (r !== 91 && r !== 123) return !1;
	let s = i ? 125 : 93;
	for (i ? lm(e, a, n.anchorStart, n.anchorEnd, n.tagStart, n.tagEnd, W.FLOW) : cm(e, a, n.anchorStart, n.anchorEnd, n.tagStart, n.tagEnd, W.FLOW), e.position++; e.input.charCodeAt(e.position) !== 0;) {
		Bm(e, t);
		let n = e.input.charCodeAt(e.position);
		if (n === s) return e.position++, pm(e), !0;
		o ? n === 44 && Y(e, "expected the node content, but found ','") : Y(e, "missed comma between flow collection entries");
		let r = !1, a = !1;
		n === 63 && vm(e.input.charCodeAt(e.position + 1)) && (r = a = !0, e.position += 1, Bm(e, t));
		let c = e.line, l = hm(e), u = Wm(e, t, Jp, !1, !0);
		Bm(e, t), n = e.input.charCodeAt(e.position), (i || a || e.line === c) && n === 58 ? (r = !0, e.position++, Bm(e, t), i || um(e, l), u || J(e), Wm(e, t, Jp, !1, !0) || J(e), Bm(e, t), i || pm(e)) : i && r ? (u || J(e), J(e)) : i ? J(e) : r && (um(e, l), u || J(e), J(e), pm(e)), n = e.input.charCodeAt(e.position), n === 44 ? (o = !0, e.position++) : o = !1;
	}
	Y(e, "unexpected end of the stream within a flow collection");
}
function Hm(e, t, n) {
	if (e.firstTabInLine !== -1 || e.input.charCodeAt(e.position) !== 45 || !ym(e.input.charCodeAt(e.position + 1))) return !1;
	for (cm(e, e.position, n.anchorStart, n.anchorEnd, n.tagStart, n.tagEnd, W.BLOCK); e.input.charCodeAt(e.position) === 45 && ym(e.input.charCodeAt(e.position + 1));) {
		e.firstTabInLine !== -1 && (e.position = e.firstTabInLine, Y(e, "tab characters must not be used in indentation"));
		let n = e.line;
		e.position++;
		let r = Z(e, !0) > 0;
		if (e.firstTabInLine !== -1 && e.input.charCodeAt(e.position) === 45 && ym(e.input.charCodeAt(e.position + 1)) && Y(e, "bad indentation of a sequence entry"), r && e.lineIndent <= t ? J(e) : Wm(e, t, Xp, !1, !0), Z(e, !0), e.lineIndent < t || e.position >= e.length) break;
		e.lineIndent > t && Y(e, "bad indentation of a sequence entry"), e.line === n && e.input.charCodeAt(e.position) === 45 && ym(e.input.charCodeAt(e.position + 1)) && Y(e, "bad indentation of a sequence entry");
	}
	return pm(e), !0;
}
function Um(e, t, n, r) {
	let i = !1, a = !1, o = !1, s = !1;
	if (e.firstTabInLine !== -1) return !1;
	let c = e.input.charCodeAt(e.position);
	for (; c !== 0;) {
		!i && e.firstTabInLine !== -1 && (e.position = e.firstTabInLine, Y(e, "tab characters must not be used in indentation"));
		let l = e.input.charCodeAt(e.position + 1), u = e.line;
		if ((c === 63 || c === 58) && ym(l)) o ||= (lm(e, e.position, r.anchorStart, r.anchorEnd, r.tagStart, r.tagEnd, W.BLOCK), !0), c === 63 ? (i && J(e), a = !0, i = !0) : (i || (J(e), a = !0), i = !1), e.position += 1, s = !0;
		else {
			i &&= (J(e), !1);
			let t = hm(e);
			if (!Wm(e, n, Yp, !1, !0)) break;
			if (e.line === u) {
				for (c = e.input.charCodeAt(e.position); _m(c);) c = e.input.charCodeAt(++e.position);
				if (c === 58) c = e.input.charCodeAt(++e.position), ym(c) || Y(e, "a whitespace character is expected after the key-value separator within a block mapping"), o ||= (e.events.splice(t.eventsLength, 0, {
					type: H.MAPPING,
					start: t.position,
					anchorStart: r.anchorStart,
					anchorEnd: r.anchorEnd,
					tagStart: r.tagStart,
					tagEnd: r.tagEnd,
					style: W.BLOCK
				}), !0), a = !0, i = !1, s = !1;
				else if (a) Y(e, "expected ':' after a mapping key");
				else return r.anchorStart !== q || r.tagStart !== q ? (gm(e, t), !1) : !0;
			} else if (a) Y(e, "can not read a block mapping entry; a multiline key may not be an implicit key");
			else return r.anchorStart !== q || r.tagStart !== q ? (gm(e, t), !1) : !0;
		}
		if (Wm(e, t, Zp, !0, s) && (s = !1), i || (s &&= (J(e), !1)), Z(e, !0), c = e.input.charCodeAt(e.position), (e.line === u || e.lineIndent > t) && c !== 0) Y(e, "bad indentation of a mapping entry");
		else if (e.lineIndent < t) break;
	}
	return a ? (i && J(e), o && pm(e), !0) : !1;
}
function Wm(e, t, n, r, i, a = !0) {
	e.depth >= e.maxDepth && Y(e, `nesting exceeded maxDepth (${e.maxDepth})`), e.depth++;
	let o = 1, s = !1, c = !1, l = null, u = mm(), d = n === Zp || n === Xp, f = d, p = d;
	if (r && Z(e, !0) && (s = !0, o = e.lineIndent > t ? 1 : e.lineIndent === t ? 0 : -1), o === 1) for (;;) {
		let r = e.input.charCodeAt(e.position), i = hm(e);
		if (s && o !== 1 && (r === 33 || r === 38)) break;
		if (s && p && (u.tagStart !== q || u.anchorStart !== q) && (r === 33 || r === 38)) {
			let n = hm(e), r = t + 1;
			if (Um(e, e.position - e.lineStart, r, u) && e.events[n.eventsLength]?.type === H.MAPPING) return e.depth--, !0;
			gm(e, n);
		}
		if (s && (r === 33 && u.tagStart !== q || r === 38 && u.anchorStart !== q) || !jm(e, u, n === Jp) && !Mm(e, u)) break;
		l === null && (l = i), Z(e, !0) ? (s = !0, f = p, o = e.lineIndent > t ? 1 : e.lineIndent === t ? 0 : -1) : f = !1;
	}
	if (f &&= s || i, o === 1 || n === Zp) {
		let r = n === Jp || n === Yp ? t : t + 1, i = e.position - e.lineStart;
		if (o === 1) {
			if (f && (Hm(e, i, u) || Um(e, i, r, u)) || Vm(e, r, u)) c = !0;
			else {
				let t = e.input.charCodeAt(e.position);
				if (l !== null && a && p && !f && t !== 124 && t !== 62) {
					let t = hm(e), n = l.position - l.lineStart;
					gm(e, l), Um(e, n, r, mm()) && e.events[t.eventsLength]?.type === H.MAPPING ? c = !0 : gm(e, t);
				}
				!c && (d && Lm(e, r, u) || Fm(e, r, u) || Im(e, r, u) || Nm(e, u) || zm(e, r, n, u)) && (c = !0);
			}
		} else o === 0 && (c = f && Hm(e, i, u));
	}
	return d &&= !c, !c && (u.anchorStart !== q || u.tagStart !== q || d) && (dm(e, q, q, u.anchorStart, u.anchorEnd, u.tagStart, u.tagEnd, U.PLAIN), c = !0), e.depth--, c || u.anchorStart !== q || u.tagStart !== q;
}
function Gm(e) {
	if (e.lineIndent > 0 || e.input.charCodeAt(e.position) !== 37) return !1;
	e.position++;
	let t = e.position;
	for (; e.input.charCodeAt(e.position) !== 0 && !vm(e.input.charCodeAt(e.position));) e.position++;
	let n = e.input.slice(t, e.position), r = [];
	for (n.length === 0 && Y(e, "directive name must not be less than one character in length"); e.input.charCodeAt(e.position) !== 0 && !X(e.input.charCodeAt(e.position));) {
		for (; _m(e.input.charCodeAt(e.position));) e.position++;
		if (e.input.charCodeAt(e.position) === 35 || X(e.input.charCodeAt(e.position)) || e.input.charCodeAt(e.position) === 0) break;
		let t = e.position;
		for (; e.input.charCodeAt(e.position) !== 0 && !vm(e.input.charCodeAt(e.position));) e.position++;
		r.push(e.input.slice(t, e.position));
	}
	if (X(e.input.charCodeAt(e.position)) && Tm(e), n === "YAML") {
		e.directives.some((e) => e.kind === "yaml") && Y(e, "duplication of %YAML directive"), r.length !== 1 && Y(e, "YAML directive accepts exactly one argument");
		let t = /^([0-9]+)\.([0-9]+)$/.exec(r[0]);
		t === null && Y(e, "ill-formed argument of the YAML directive"), parseInt(t[1], 10) !== 1 && Y(e, "unacceptable YAML version of the document"), e.directives.push({
			kind: "yaml",
			version: r[0]
		});
	} else if (n === "TAG") {
		r.length !== 2 && Y(e, "TAG directive accepts exactly two arguments");
		let [t, n] = r;
		em.test(t) || Y(e, "ill-formed tag handle (first argument) of the TAG directive"), qp.call(e.tagHandlers, t) && Y(e, `there is a previously declared suffix for "${t}" tag handle`), am.test(n) || Y(e, "ill-formed tag prefix (second argument) of the TAG directive"), e.tagHandlers[t] = n, e.directives.push({
			kind: "tag",
			handle: t,
			prefix: n
		});
	}
	return !0;
}
function Km(e) {
	e.directives = [], e.tagHandlers = Object.create(null);
	let t = !1;
	for (Z(e, !0); Gm(e);) t = !0, Z(e, !0);
	let n = !1, r = !1, i = !0;
	if (e.lineIndent === 0 && e.input.charCodeAt(e.position) === 45 && e.input.charCodeAt(e.position + 1) === 45 && e.input.charCodeAt(e.position + 2) === 45 && ym(e.input.charCodeAt(e.position + 3))) {
		n = !0;
		let t = e.line;
		e.position += 3, Z(e, !0), i = e.line > t;
	} else t && Y(e, "directives end mark is expected");
	let a = e.events.length;
	if (!n && e.position === e.lineStart && e.input.charCodeAt(e.position) === 46 && Em(e)) {
		e.position += 3, Z(e, !0);
		return;
	}
	if (sm(e, n, !1), Wm(e, e.lineIndent - 1, Zp, !1, i, i) || J(e), Z(e, !0), e.position === e.lineStart && Em(e) && (r = e.input.charCodeAt(e.position) === 46, r)) {
		let t = e.line;
		e.position += 3, Z(e, !0), e.line === t && e.position < e.length && Y(e, "end of the stream or a document separator is expected");
	}
	let o = e.events[a];
	o?.type === H.DOCUMENT && (o.explicitEnd = r), pm(e), !r && e.position < e.length && !Om(e) && Y(e, "end of the stream or a document separator is expected");
}
function qm(e, t) {
	let n = e.length, r = {
		...om,
		...t,
		input: `${e}\0`,
		length: n,
		position: 0,
		line: 0,
		lineStart: 0,
		lineIndent: 0,
		firstTabInLine: -1,
		depth: 0,
		directives: [],
		tagHandlers: Object.create(null),
		events: []
	}, i = e.indexOf("\0");
	for (i !== -1 && pp.throwAt(e, i, "null byte is not allowed in input", r.filename); r.position < r.length && (Dm(r), Z(r, !0), !(r.position >= r.length));) {
		let e = r.position;
		Km(r), r.position === e && 
		/* c8 ignore next */
		Y(r, "can not read a document");
	}
	return r.events;
}
var Jm = {
	...om,
	...Pp
};
function Ym(e, t = {}) {
	let n = {
		...Jm,
		...t
	}, r = String(e), i = Object.keys(om), a = Object.keys(Pp);
	return Kp(qm(r, Yf(n, i)), {
		...Yf(n, a),
		source: r
	});
}
function Xm(e, t) {
	let n = Ym(e, t);
	if (n.length === 0) throw new pp("expected a document, but the input is empty");
	if (n.length === 1) return n[0];
	throw new pp("expected a single document in the stream, but found more");
}
var Zm = Symbol("INVALID");
function Qm(e) {
	let t = new Set([
		e.defaultScalarTag,
		e.defaultSequenceTag,
		e.defaultMappingTag
	].filter((e) => e !== void 0)), n = e.implicitScalarTags, r = e.tags.filter((e) => !(e.nodeKind === "scalar" && e.implicit) && !t.has(e)), i = e.tags.filter((e) => t.has(e));
	return [
		...n.map((e) => ({
			tag: e,
			implicitTag: !0
		})),
		...r.map((e) => ({
			tag: e,
			implicitTag: !1
		})),
		...i.map((e) => ({
			tag: e,
			implicitTag: !0
		}))
	];
}
function $m(e, t) {
	for (let n = 0, r = e.representTypes.length; n < r; n += 1) {
		let { tag: r, implicitTag: i } = e.representTypes[n];
		if (r.identify(t)) {
			let e;
			return e = r.matchByTagPrefix ? r.representTagName(t) : r.tagName, {
				tag: r,
				tagName: e,
				implicitTag: i
			};
		}
	}
	return null;
}
function eh(e, t) {
	if (!e.noRefs && typeof t == "object" && t) {
		let n = e.refs.get(t);
		if (n) return n.anchor === void 0 && (n.anchor = `ref_${e.refCounter++}`), {
			kind: "alias",
			anchor: n.anchor
		};
	}
	let n = $m(e, t);
	if (!n) {
		if (t === void 0 || e.skipInvalid) return Zm;
		throw new pp(`unacceptable kind of an object to dump ${Object.prototype.toString.call(t)}`);
	}
	let { tag: r, tagName: i, implicitTag: a } = n, o = a ? i : jp(i);
	if (r.nodeKind === "scalar") return {
		kind: "scalar",
		tag: o,
		tagged: !a,
		style: U.PLAIN,
		value: r.represent(t)
	};
	if (r.nodeKind === "sequence") {
		let n = r.represent(t), i = {
			kind: "sequence",
			tag: o,
			tagged: !a,
			style: W.BLOCK,
			items: []
		};
		e.noRefs || e.refs.set(t, i);
		for (let t = 0, r = n.length; t < r; t += 1) {
			let r = eh(e, n[t]);
			r === Zm && n[t] === void 0 && (r = eh(e, null)), r !== Zm && i.items.push(r);
		}
		return i;
	}
	let s = r.represent(t), c = {
		kind: "mapping",
		tag: o,
		tagged: !a,
		style: W.BLOCK,
		items: []
	};
	e.noRefs || e.refs.set(t, c);
	for (let [t, n] of s) {
		let r = eh(e, t);
		if (r === Zm) continue;
		let i = eh(e, n);
		i !== Zm && c.items.push({
			key: r,
			value: i
		});
	}
	return c;
}
function th(e, t, n = {}) {
	let r = eh({
		representTypes: Qm(t),
		noRefs: n.noRefs ?? !1,
		skipInvalid: n.skipInvalid ?? !1,
		refs: /* @__PURE__ */ new Map(),
		refCounter: 0
	}, e);
	return [{
		contents: r === Zm ? null : r,
		directives: []
	}];
}
var nh = Symbol("visit:break"), rh = Symbol("visit:skip");
function ih(e, t, n) {
	let r = t(e, n);
	if (r === nh) return !0;
	if (r === rh) return !1;
	let i = n.depth + 1;
	switch (e.kind) {
		case "sequence":
			for (let n of e.items) if (ih(n, t, {
				depth: i,
				parent: e,
				isKey: !1
			})) return !0;
			break;
		case "mapping": for (let { key: n, value: r } of e.items) if (ih(n, t, {
			depth: i,
			parent: e,
			isKey: !0
		}) || ih(r, t, {
			depth: i,
			parent: e,
			isKey: !1
		})) return !0;
	}
	return !1;
}
function ah(e, t) {
	for (let n of e) if (n.contents && ih(n.contents, t, {
		depth: 0,
		parent: null,
		isKey: !1
	})) return;
}
function oh(e, t) {
	return !!(e & 1 << t);
}
var sh = {
	applyQuoteFlowKeysOption: lh,
	doubleQuoteForInvisibles: uh,
	doubleQuoteWhitespaceOnly: dh,
	applyForceQuotesOption: fh,
	tryLongOrMultilineAsBlock: ph,
	quoteInvalidPlain: mh,
	fallbackToDoubleQuoted: hh
};
function ch(e) {
	return e.presenterOptions.quoteStyle === "single" && oh(e.allowedStylesMask, U.SINGLE_QUOTED) ? U.SINGLE_QUOTED : U.DOUBLE_QUOTED;
}
function lh(e) {
	e.presenterOptions.quoteFlowKeys && e.isKey && e.flowOnly && e.style === U.PLAIN && (e.style = U.DOUBLE_QUOTED);
}
function uh(e) {
	e.style === U.PLAIN && /[\t\x7F-\xA0\u2028\u2029\uFEFF\uFFFE\uFFFF]/.test(e.node.value) && (e.style = U.DOUBLE_QUOTED);
}
function dh(e) {
	e.style === U.PLAIN && /^\s+$/.test(e.node.value) && (e.style = U.DOUBLE_QUOTED);
}
function fh(e) {
	e.presenterOptions.forceQuotes && (e.isKey || e.style !== U.PLAIN || e.node.tag === e.presenterOptions.schema.defaultScalarTag.tagName && (e.style = e.node.value.includes("\n") ? U.DOUBLE_QUOTED : ch(e)));
}
function ph(e) {
	if (e.style !== U.PLAIN || e.isKey) return;
	let t = e.node.value, n = t.indexOf("\n") !== -1;
	if (!oh(e.allowedStylesMask, U.LITERAL_BLOCK)) {
		n && (e.style = U.DOUBLE_QUOTED);
		return;
	}
	let r = e.presenterOptions.lineWidth;
	if (r === -1) {
		n && (e.style = U.LITERAL_BLOCK);
		return;
	}
	let i = Math.max(Math.min(r, 40), r - e.shiftOfContent), a = 0, o = !1;
	for (; a <= t.length;) {
		let e = t.length, n = t.indexOf("\n", a);
		n !== -1 && (e = n);
		let r = t.slice(a, e);
		if (r.length > i && r[0] !== " " && / [^ \t]/.test(r) && (o = !0), n === -1) break;
		a = n + 1;
	}
	o ? e.style = U.FOLDED_BLOCK : n && (e.style = U.LITERAL_BLOCK);
}
function mh(e) {
	e.style === U.PLAIN && !oh(e.allowedStylesMask, U.PLAIN) && (e.style = ch(e));
}
function hh(e) {
	oh(e.allowedStylesMask, e.style) || (e.style = U.DOUBLE_QUOTED);
}
function gh(e, t) {
	return e | 1 << t;
}
var _h = "[\\x09\\x0A\\x0D\\x20-\\x7E\\x85\\xA0-\\uD7FF\\uE000-\\uFFFD\\u{10000}-\\u{10FFFF}]", vh = "[\\n\\r]", yh = "\\uFEFF", bh = "[ \\t]", xh = `(?:(?!(?:${vh}|${yh}))${_h})`, Sh = `(?:(?!${bh})${xh})`, Ch = "[\\x09\\x20-\\uD7FF\\uE000-\\uFFFF\\u{10000}-\\u{10FFFF}]", wh = "[-?:,\\[\\]{}#&*!|>'\"%@`]", Th = "[,\\[\\]{}]", Eh = Sh, Dh = `(?:(?!${Th})${Sh})`, Oh = `(?:(?:(?!${wh})${Sh})|[?:-](?=${Eh}))`, kh = `(?:(?:(?!${wh})${Sh})|[?:-](?=${Dh}))`, Ah = `(?:(?:(?![:#])${Eh})|:(?=${Eh}))#*`, jh = `(?:(?:(?![:#])${Dh})|:(?=${Dh}))#*`, Mh = `(?:${bh}*${Ah})*`, Nh = `(?:${bh}*${jh})*`, Ph = `${Oh}#*${Mh}`, Fh = `${kh}#*${Nh}`, Ih = Ph, Lh = Fh, Rh = `\\n+${Ah}${Mh}`, zh = `\\n+${jh}${Nh}`, Bh = `${Ph}(?:${Rh})*`, Vh = `${Fh}(?:${zh})*`, Hh = RegExp(`^(?:${Bh})$`, "u"), Uh = RegExp(`^(?:${Vh})$`, "u"), Wh = RegExp(`^(?:${Ih})$`, "u"), Gh = RegExp(`^(?:${Lh})$`, "u"), Kh = RegExp(`^(?:${Ch})*$`, "u"), qh = RegExp(`^(?:${Ch}|\\n)*$`, "u"), Jh = RegExp(`^(?:${xh}|\\n)*$`, "u"), Yh = /^(?:---|\.\.\.)(?=$|[ \t\n\r])/, Xh = /^(?:---|\.\.\.)(?=$|[ \t\n\r])/m;
function Zh(e) {
	let t = e.node.value;
	if (t !== "") {
		if (!(e.isKey ? e.flowOnly ? Gh : Wh : e.flowOnly ? Uh : Hh).test(t) || e.shiftOfFirstLine === 0 && Yh.test(t)) return !1;
		if (e.shiftOfContent === 0) {
			let e = t.indexOf("\n");
			if (e !== -1) {
				let n = t.slice(e + 1);
				if (Xh.test(n)) return !1;
			}
		}
	}
	let n = e.presenterOptions.schema.resolveImplicitScalarTag(t).tag.tagName;
	return !(!e.node.tagged && n !== e.node.tag || !e.node.tagged && t === "=" && n === e.presenterOptions.schema.defaultScalarTag.tagName);
}
function Qh(e) {
	let t = e.node.value;
	if (!(e.isKey ? Kh : qh).test(t) || /[ \t]\n|\n[ \t]/.test(t)) return !1;
	if (!e.isKey && e.shiftOfContent === 0) {
		let e = t.indexOf("\n");
		if (e !== -1 && Xh.test(t.slice(e + 1))) return !1;
	}
	return !0;
}
function $h(e) {
	if (e.flowOnly || !Jh.test(e.node.value)) return !1;
	let t = e.shiftOfContent - e.shiftOfParent;
	return !(t < 1 || t > 9 && /^\n* /.test(e.node.value) || e.shiftOfContent === 0 && Xh.test(e.node.value));
}
function eg(e) {
	let t = gh(0, U.DOUBLE_QUOTED);
	Zh(e) && (t = gh(t, U.PLAIN)), Qh(e) && (t = gh(t, U.SINGLE_QUOTED)), $h(e) && (t = gh(gh(t, U.LITERAL_BLOCK), U.FOLDED_BLOCK)), e.allowedStylesMask = t;
}
function tg(e) {
	switch (e.style) {
		case U.PLAIN: return ng(e);
		case U.SINGLE_QUOTED: return rg(e);
		case U.LITERAL_BLOCK: return ig(e);
		case U.FOLDED_BLOCK: return ag(e);
		case U.DOUBLE_QUOTED: return og(e);
	}
}
function ng(e) {
	return sg(e.node.value, e.shiftOfContent);
}
function rg(e) {
	return `'${sg(e.node.value, e.shiftOfContent).replace(/'/g, "''")}'`;
}
function ig(e) {
	let t = e.node.value;
	return "|" + ug(t, e.shiftOfParent, e.shiftOfContent) + dg(cg(t, e.shiftOfContent));
}
function ag(e) {
	let t = e.node.value, n = e.presenterOptions.lineWidth, r = Infinity;
	return n !== -1 && (r = Math.max(Math.min(n, 40), n - e.shiftOfContent)), ">" + ug(t, e.shiftOfParent, e.shiftOfContent) + dg(cg(mg(t, r), e.shiftOfContent));
}
function og(e) {
	return `"${_g(e.node.value)}"`;
}
function sg(e, t) {
	let n = e.indexOf("\n");
	if (n === -1) return e;
	let r = " ".repeat(t), i = e.slice(0, n), a = /(\n+)([^\n]*)/g;
	a.lastIndex = n;
	let o;
	for (; o = a.exec(e);) {
		let e = o[1].length, t = o[2];
		i += "\n".repeat(e + 1) + r + t;
	}
	return i;
}
function cg(e, t) {
	let n = " ".repeat(t), r = 0, i = "", a = e.length;
	for (; r < a;) {
		let t, o = e.indexOf("\n", r);
		o === -1 ? (t = e.slice(r), r = a) : (t = e.slice(r, o + 1), r = o + 1), t.length && t !== "\n" && (i += n), i += t;
	}
	return i;
}
function lg(e) {
	return /^\n* /.test(e);
}
function ug(e, t, n) {
	let r = lg(e) ? String(n - t) : "", i = e[e.length - 1] === "\n";
	return `${r}${i && (e[e.length - 2] === "\n" || e === "\n") ? "+" : i ? "" : "-"}\n`;
}
function dg(e) {
	return e[e.length - 1] === "\n" ? e.slice(0, -1) : e;
}
function fg(e) {
	return e === " " || e === "	";
}
function pg(e, t) {
	if (e === "" || fg(e[0])) return e;
	let n = / [^ \t]/g, r, i = 0, a, o = 0, s = 0, c = "";
	for (; r = n.exec(e);) s = r.index, s - i > t && (a = o > i ? o : s, c += `\n${e.slice(i, a)}`, i = a + 1), o = s;
	return c += "\n", e.length - i > t && o > i ? c += `${e.slice(i, o)}\n${e.slice(o + 1)}` : c += e.slice(i), c.slice(1);
}
function mg(e, t) {
	let n = /(\n+)([^\n]*)/g, r = e.indexOf("\n");
	r === -1 && (r = e.length), n.lastIndex = r;
	let i = pg(e.slice(0, r), t), a = e[0] === "\n" || fg(e[0]), o, s;
	for (; s = n.exec(e);) {
		let e = s[1], n = s[2];
		o = n !== "" && fg(n[0]), i += e + (!a && !o && n !== "" ? "\n" : "") + pg(n, t), a = o;
	}
	return i;
}
var hg = /["\\\x00-\x1F\x7F-\xA0\u2028\u2029\uD800-\uDFFF\uFEFF\uFFFE\uFFFF]/gu;
function gg(e) {
	switch (e) {
		case "\0": return "\\0";
		case "\x07": return "\\a";
		case "\b": return "\\b";
		case "	": return "\\t";
		case "\n": return "\\n";
		case "\v": return "\\v";
		case "\f": return "\\f";
		case "\r": return "\\r";
		case "\x1B": return "\\e";
		case "\"": return "\\\"";
		case "\\": return "\\\\";
		case "": return "\\N";
		case "\xA0": return "\\_";
		case "\u2028": return "\\L";
		case "\u2029": return "\\P";
	}
	let t = e.charCodeAt(0), n = t.toString(16).toUpperCase();
	return t <= 255 ? `\\x${"0".repeat(2 - n.length)}${n}` : `\\u${"0".repeat(4 - n.length)}${n}`;
}
function _g(e) {
	return e.replace(hg, gg);
}
var vg = 10, yg = {
	indent: 2,
	seqNoIndent: !1,
	seqInlineFirst: !0,
	lineWidth: 80,
	flowBracketPadding: !1,
	flowSkipCommaSpace: !1,
	flowSkipColonSpace: !1,
	quoteFlowKeys: !1,
	quoteStyle: "single",
	forceQuotes: !1,
	scalarStyleRules: Object.keys(sh).map((e) => Reflect.get(sh, e)),
	tagBeforeAnchor: !1
};
function bg(e) {
	return e.tagged ? e.tag : jp(e.tag);
}
function xg(e) {
	let t = {
		...yg,
		...e
	};
	return t.flowSkipColonSpace && (t.quoteFlowKeys = !0), {
		...t,
		defaultScalarTagName: t.schema.defaultScalarTag.tagName,
		openEnded: !1
	};
}
function Sg(e, t) {
	return `\n${" ".repeat(e.indent * t)}`;
}
function Cg(e, t, n, r, i, a) {
	return {
		node: t,
		parent: n,
		level: r,
		isKey: i,
		flowOnly: a,
		shiftOfParent: r === 0 ? -1 : e.indent * (r - 1),
		shiftOfContent: e.indent * Math.max(1, r),
		shiftOfFirstLine: r === 0 ? 0 : e.indent * r,
		presenterOptions: e,
		allowedStylesMask: 0,
		style: t.style
	};
}
function wg(e, t, n) {
	let r = "";
	for (let i = 0, a = n.items.length; i < a; i += 1) {
		let a = kg(e, t, n.items[i], n, {}).text;
		i > 0 && (r += `,${e.flowSkipCommaSpace ? "" : " "}`), r += a;
	}
	let i = e.flowBracketPadding && n.items.length > 0 ? " " : "";
	return `[${i}${r}${i}]`;
}
function Tg(e, t, n, r) {
	let i = "";
	for (let a = 0, o = n.items.length; a < o; a += 1) {
		let o = kg(e, t + 1, n.items[a], n, {
			block: !0,
			compact: e.seqInlineFirst,
			isblockseq: !0
		}).text;
		(!r || i !== "") && (i += Sg(e, t)), o === "" || vg === o.charCodeAt(0) ? i += "-" : i += "- ", i += o;
	}
	return i;
}
function Eg(e, t, n) {
	let r = "";
	for (let { key: i, value: a } of n.items) {
		let o = "";
		r !== "" && (o += `,${e.flowSkipCommaSpace ? "" : " "}`);
		let s = kg(e, t, i, n, { iskey: !0 }), c = s.text, l = kg(e, t, a, n, {}).text, u = e.flowSkipColonSpace || l === "" ? "" : " ", d = i.kind === "scalar" && s.noBody && (i.tagged || i.anchor !== void 0), f = i.kind === "alias" || d ? " " : "";
		o += `${c}${f}:${u}${l}`, r += o;
	}
	let i = e.flowBracketPadding && r !== "" ? " " : "";
	return `{${i}${r}${i}}`;
}
function Dg(e, t, n, r) {
	let i = "";
	for (let a = 0, o = n.items.length; a < o; a += 1) {
		let o = "";
		(!r || i !== "") && (o += Sg(e, t));
		let { key: s, value: c } = n.items[a], l = (s.kind === "mapping" || s.kind === "sequence") && s.style === W.BLOCK && s.items.length !== 0 || s.kind === "scalar" && (s.style === U.LITERAL_BLOCK || s.style === U.FOLDED_BLOCK), u = l ? kg(e, t + 1, s, n, {
			block: !0,
			compact: !0,
			isblockseq: !Og(e, s, t + 1)
		}) : kg(e, t + 1, s, n, {
			block: !0,
			compact: !0,
			iskey: !0
		}), d = u.text, f = s.kind === "scalar" && s.value.indexOf("\n") !== -1, p = d.length > 1024 && /^[\s\S]{1025}/u.test(d), ee = l || f || p;
		ee && (d && vg === d.charCodeAt(0) ? o += "?" : o += "? "), o += d, ee && (o += Sg(e, t));
		let te = kg(e, t + 1, c, n, {
			block: !0,
			compact: ee,
			isblockseq: ee && !Og(e, c, t + 1)
		}).text, ne = s.kind === "scalar" && u.noBody && (s.tagged || s.anchor !== void 0), re = !ee && (s.kind === "alias" || ne) ? " " : "";
		te === "" || vg === te.charCodeAt(0) ? o += `${re}:` : o += `${re}: `, o += te, i += o;
	}
	return i;
}
function Og(e, t, n) {
	return t.kind === "alias" || t.tagged || t.anchor !== void 0 || e.indent < 2 && n > 0;
}
function kg(e, t, n, r, i) {
	if (n.kind === "alias") return e.openEnded = !1, {
		text: `*${n.anchor}`,
		noBody: !1
	};
	let { block: a = !1, iskey: o = !1, isblockseq: s = !1 } = i, c = i.compact ?? !1, l = n.anchor !== void 0;
	Og(e, n, t) && (c = !1);
	let u, d = n.tagged, f = a && (n.kind === "mapping" || n.kind === "sequence") && n.style === W.BLOCK && n.items.length !== 0;
	if (n.kind === "mapping") u = f ? Dg(e, t, n, c) : Eg(e, t, n);
	else if (n.kind === "sequence") u = f ? e.seqNoIndent && !s && t > 0 ? Tg(e, t - 1, n, c) : Tg(e, t, n, c) : wg(e, t, n);
	else {
		let i = Cg(e, n, r, t, o, !a);
		eg(i);
		for (let t of e.scalarStyleRules) t(i);
		u = tg(i), e.openEnded = (i.style === U.LITERAL_BLOCK || i.style === U.FOLDED_BLOCK) && (n.value === "\n" || n.value.endsWith("\n\n")), d = n.tagged || u === "" && i.flowOnly && r?.kind === "sequence" && !l || i.style !== U.PLAIN && n.tag !== e.defaultScalarTagName;
	}
	(n.kind === "mapping" || n.kind === "sequence") && !f && (e.openEnded = !1), f && c && t > 0 && e.indent > 2 && (u = `${" ".repeat(e.indent - 2)}${u}`);
	let p = u === "", ee = u;
	if (d || l) {
		let t = [], r = d ? bg(n) : null, i = l ? `&${n.anchor}` : null;
		e.tagBeforeAnchor ? (r !== null && t.push(r), i !== null && t.push(i)) : (i !== null && t.push(i), r !== null && t.push(r));
		let a = u === "" || u.charCodeAt(0) === vg ? "" : " ";
		ee = `${t.join(" ")}${a}${u}`;
	}
	return {
		text: ee,
		noBody: p
	};
}
function Ag(e) {
	return (e.kind === "sequence" || e.kind === "mapping") && e.style === W.BLOCK && e.items.length !== 0 && !e.tagged && e.anchor === void 0;
}
function jg(e) {
	let t = "";
	for (let n of e.directives) {
		if (n.kind === "yaml") {
			t += `%YAML ${n.version}\n`;
			continue;
		}
		let { handle: e, prefix: r } = n;
		t += `%TAG ${e} ${r}\n`;
	}
	return t;
}
function Mg(e, t) {
	let n = xg(t), r = "", i = !1;
	for (let t = 0; t < e.length; t += 1) {
		let a = e[t];
		n.openEnded = !1;
		let o = jg(a), s = o !== "", c = a.explicitStart || s || t > 0 && !i;
		if (r += o, a.contents === null) c && (r += "---\n");
		else if (c) {
			let e = kg(n, 0, a.contents, null, {
				block: !0,
				compact: !0
			}).text, t = e === "" ? "" : s || Ag(a.contents) ? "\n" : " ";
			r += `---${t}${e}\n`;
		} else r += kg(n, 0, a.contents, null, {
			block: !0,
			compact: !0
		}).text + "\n";
		i = a.explicitEnd || n.openEnded, i && (r += "...\n");
	}
	return r;
}
var Ng = {
	...yg,
	schema: op,
	skipInvalid: !1,
	noRefs: !1,
	flowLevel: -1,
	sortKeys: !1,
	transform: () => {}
};
function Pg(e, t) {
	let n = String(e), r = String(t);
	return n < r ? -1 : +(n > r);
}
function Fg(e, t = {}) {
	let n = {
		...Ng,
		...t
	}, r = th(e, n.schema, {
		noRefs: n.noRefs,
		skipInvalid: n.skipInvalid
	});
	if (n.flowLevel >= 0 && ah(r, (e, t) => {
		if (!(t.depth < n.flowLevel)) return (e.kind === "sequence" || e.kind === "mapping") && (e.style = W.FLOW), rh;
	}), n.sortKeys) {
		let e = n.sortKeys === !0 ? Pg : n.sortKeys;
		ah(r, (t) => {
			t.kind === "mapping" && t.items.sort((t, n) => e(t.key.kind === "scalar" ? t.key.value : "", n.key.kind === "scalar" ? n.key.value : ""));
		});
	}
	return n.transform(r), Mg(r, {
		...Yf(n, Object.keys(yg)),
		schema: n.schema
	});
}
H.DOCUMENT, H.SEQUENCE, H.MAPPING, H.SCALAR, H.ALIAS, H.POP, U.PLAIN, U.SINGLE_QUOTED, U.DOUBLE_QUOTED, U.LITERAL_BLOCK, U.FOLDED_BLOCK, W.BLOCK, W.FLOW, G.CLIP, G.STRIP, G.KEEP, D.Admin.AdminMessage_ConfigType.DEVICE_CONFIG, D.Admin.AdminMessage_ConfigType.POSITION_CONFIG, D.Admin.AdminMessage_ConfigType.POWER_CONFIG, D.Admin.AdminMessage_ConfigType.NETWORK_CONFIG, D.Admin.AdminMessage_ConfigType.DISPLAY_CONFIG, D.Admin.AdminMessage_ConfigType.LORA_CONFIG, D.Admin.AdminMessage_ConfigType.BLUETOOTH_CONFIG, D.Admin.AdminMessage_ConfigType.SECURITY_CONFIG, D.Admin.AdminMessage_ConfigType.DEVICEUI_CONFIG, D.Admin.AdminMessage_ModuleConfigType.MQTT_CONFIG, D.Admin.AdminMessage_ModuleConfigType.SERIAL_CONFIG, D.Admin.AdminMessage_ModuleConfigType.EXTNOTIF_CONFIG, D.Admin.AdminMessage_ModuleConfigType.STOREFORWARD_CONFIG, D.Admin.AdminMessage_ModuleConfigType.RANGETEST_CONFIG, D.Admin.AdminMessage_ModuleConfigType.TELEMETRY_CONFIG, D.Admin.AdminMessage_ModuleConfigType.CANNEDMSG_CONFIG, D.Admin.AdminMessage_ModuleConfigType.AUDIO_CONFIG, D.Admin.AdminMessage_ModuleConfigType.REMOTEHARDWARE_CONFIG, D.Admin.AdminMessage_ModuleConfigType.NEIGHBORINFO_CONFIG, D.Admin.AdminMessage_ModuleConfigType.AMBIENTLIGHTING_CONFIG, D.Admin.AdminMessage_ModuleConfigType.DETECTIONSENSOR_CONFIG, D.Admin.AdminMessage_ModuleConfigType.PAXCOUNTER_CONFIG, D.Admin.AdminMessage_ModuleConfigType.STATUSMESSAGE_CONFIG, D.Admin.AdminMessage_ModuleConfigType.TRAFFICMANAGEMENT_CONFIG, D.Admin.AdminMessage_ModuleConfigType.TAK_CONFIG;
var Q = {
	pasoActual: 1,
	modo: "asistente",
	rolSeleccionado: "CLIENT_MUTE",
	potenciaTx: 27,
	provincia: "",
	nodoConectado: !1,
	dispositivo: null,
	transporte: null,
	myNodeNum: null,
	myNodeInfo: null,
	ownerName: "",
	ownerShort: "",
	isUnmessagable: !1,
	sessionPasskey: null,
	configSections: {},
	moduleConfigSections: {},
	channelMap: /* @__PURE__ */ new Map(),
	liveConfig: null,
	desiredConfig: null,
	qrInstance: null,
	pendingAdminResponses: []
}, Ig = /* @__PURE__ */ new Map();
function Lg(e, t, n) {
	let r = JSON.parse(JSON.stringify(e || {})), i = JSON.parse(JSON.stringify(t || {}));
	r.device && (typeof r.device.role == "number" && D.Config.Config_DeviceConfig_Role[r.device.role] && (r.device.role = D.Config.Config_DeviceConfig_Role[r.device.role]), typeof r.device.rebroadcastMode == "number" && D.Config.Config_DeviceConfig_RebroadcastMode[r.device.rebroadcastMode] && (r.device.rebroadcastMode = D.Config.Config_DeviceConfig_RebroadcastMode[r.device.rebroadcastMode])), r.lora && (typeof r.lora.region == "number" && D.Config.Config_LoRaConfig_RegionCode[r.lora.region] && (r.lora.region = D.Config.Config_LoRaConfig_RegionCode[r.lora.region]), typeof r.lora.modemPreset == "number" && D.Config.Config_LoRaConfig_ModemPreset[r.lora.modemPreset] && (r.lora.modemPreset = D.Config.Config_LoRaConfig_ModemPreset[r.lora.modemPreset]));
	let a;
	return Array.isArray(n) && n.length > 0 && (a = n.map((e) => {
		let t = JSON.parse(JSON.stringify(e || {})), n = t.role;
		return typeof n == "number" && D.Channel.Channel_Role[n] && (n = D.Channel.Channel_Role[n]), {
			index: t.index ?? 0,
			role: n ?? "DISABLED",
			settings: t.settings || {}
		};
	})), {
		config: r,
		moduleConfig: i,
		channels: a
	};
}
function $(e) {
	let t = `[${(/* @__PURE__ */ new Date()).toLocaleTimeString()}] ${e}\n`, n = document.getElementById("logTextarea");
	n && (n.value = t + n.value);
}
function Rg() {
	let e = document.getElementById("inputLongName")?.value.trim() || "MiNodo-Andalucia", t = (document.getElementById("inputShortName")?.value.trim() || "AND1").slice(0, 4), n = Q.rolSeleccionado === "CLIENT_MUTE", r = n ? 4 : 3, i = document.getElementById("posicionSelect")?.value, a = i !== void 0 && i !== "" ? Number(i) : n ? 21600 : 259200, o = Number(document.querySelector("input[name=\"txPowerSelect\"]:checked")?.value || 27), s = Number(document.getElementById("telemetriaSelect")?.value || 0), c = !!document.getElementById("chkMqtt")?.checked, l = document.getElementById("provinciaSelect")?.value || "", u = [{
		index: 0,
		role: "PRIMARY",
		settings: {
			name: "SFNarrow",
			psk: "AQ==",
			uplinkEnabled: !0,
			downlinkEnabled: !n,
			moduleSettings: { positionPrecision: 15 }
		}
	}], d = 1;
	l && u.push({
		index: d++,
		role: "SECONDARY",
		settings: {
			name: l,
			psk: "AQ==",
			uplinkEnabled: !0,
			downlinkEnabled: !0,
			moduleSettings: { positionPrecision: 15 }
		}
	});
	let f = c && !!document.getElementById("chkMqttMap")?.checked;
	for (let e of [
		{
			id: "chkIberia",
			name: "Iberia"
		},
		{
			id: "chkAndalucia",
			name: "Andalucia"
		},
		{
			id: "chkTest",
			name: "Test"
		},
		{
			id: "chkBots",
			name: "Bots"
		},
		{
			id: "chkSos",
			name: "sos"
		}
	]) document.getElementById(e.id)?.checked && d < 8 && u.push({
		index: d++,
		role: "SECONDARY",
		settings: {
			name: e.name,
			psk: "AQ==",
			uplinkEnabled: !0,
			downlinkEnabled: !0,
			moduleSettings: { positionPrecision: 15 }
		}
	});
	for (; d < 8;) u.push({
		index: d++,
		role: "SECONDARY",
		settings: {}
	});
	let p = { telemetry: { deviceUpdateInterval: s } };
	if (c) {
		let e = {
			enabled: !0,
			address: "mqtt.desdechipiona.es",
			username: "meshdev",
			password: "large4cats",
			root: "msh/EU_868",
			encryptionEnabled: !0,
			tlsEnabled: !0,
			proxyToClientEnabled: !0,
			mapReportingEnabled: f
		};
		f && (e.mapReportSettings = {
			publishIntervalSecs: 259200,
			positionPrecision: 14,
			shouldReportLocation: !0
		}), p.mqtt = e;
	}
	let ee = {
		owner: e,
		owner_short: t,
		is_unmessagable: !1,
		config: {
			device: {
				role: Q.rolSeleccionado,
				nodeInfoBroadcastSecs: 259200,
				rebroadcastMode: n ? "CORE_PORTNUMS_ONLY" : "ALL",
				disableTripleClick: !0,
				tzdef: "GMT-1GMT,M3.5.0,M10.5.0/3"
			},
			lora: {
				region: "EU_868",
				usePreset: !1,
				bandwidth: 62,
				spreadFactor: 7,
				codingRate: 5,
				channelNum: 4,
				hopLimit: r,
				txPower: o,
				txEnabled: !0,
				sx126xRxBoostedGain: !0,
				ignoreMqtt: !!c,
				configOkToMqtt: !!f
			},
			position: {
				positionBroadcastSmartEnabled: !1,
				positionBroadcastSecs: a,
				positionFlags: n ? 0 : 137,
				fixedPosition: !n
			},
			security: { serialEnabled: !0 }
		},
		module_config: p,
		channels: u
	};
	Q.desiredConfig = ee;
	let te = Fg(ee, {
		lineWidth: 120,
		noRefs: !0,
		sortKeys: !1
	}), ne = document.getElementById("desiredYamlTextarea");
	return ne && (ne.value = te), {
		configDoc: ee,
		yamlText: te
	};
}
function zg(e) {
	let t = [];
	for (; e > 127;) t.push(e & 127 | 128);
	return t.push(e & 127), t;
}
function Bg(e, t = [1], n = !0, r = !0) {
	let i = new TextEncoder().encode(e), a = [
		18,
		t.length,
		...t,
		26,
		i.length,
		...i,
		40,
		+!!n,
		48,
		+!!r
	];
	return [
		10,
		...zg(a.length),
		...a
	];
}
function Vg(e = 62, t = 7, n = 5, r = 4, i = 4, a = 27) {
	let o = [
		24,
		...zg(e),
		32,
		...zg(t),
		40,
		...zg(n),
		56,
		3,
		64,
		...zg(i),
		80,
		...zg(a),
		88,
		...zg(r)
	];
	return [
		18,
		...zg(o.length),
		...o
	];
}
function Hg(e) {
	let t = [];
	if (Array.isArray(e.channels)) {
		for (let n of e.channels) if (n.settings && n.settings.name) {
			let r = n.role === "PRIMARY" ? e.config.device.role !== "CLIENT_MUTE" : n.settings.downlinkEnabled ?? !0, i = n.settings.uplinkEnabled ?? !0;
			t.push(...Bg(n.settings.name, [1], i, r));
		}
	}
	let n = e.config.lora;
	t.push(...Vg(n.bandwidth, n.spreadFactor, n.codingRate, n.channelNum, n.hopLimit, n.txPower));
	let r = new Uint8Array(t), i = "";
	for (let e = 0; e < r.byteLength; e++) i += String.fromCharCode(r[e]);
	return `https://meshtastic.org/e/#${btoa(i).replaceAll("+", "-").replaceAll("/", "_").replaceAll("=", "")}`;
}
function Ug() {
	let e = document.getElementById("chkMqtt"), t = document.getElementById("chkMqttMap"), n = document.getElementById("mqttMapOptionWrapper");
	e && n && (e.checked ? n.style.display = "block" : (n.style.display = "none", t && (t.checked = !1)));
	let { configDoc: r } = Rg(), i = r.owner, a = r.owner_short, o = document.getElementById("previewAvatar"), s = document.getElementById("previewLongName"), c = document.getElementById("previewShortName");
	o && (o.textContent = i.charAt(0).toUpperCase()), s && (s.textContent = i), c && (c.textContent = a);
	try {
		let e = Hg(r), t = document.getElementById("qrShareUrl");
		t && (t.value = e);
		let n = document.getElementById("qrCanvasContainer");
		n && typeof window.QRCode == "function" && (n.innerHTML = "", new window.QRCode(n, {
			text: e,
			width: 220,
			height: 220,
			colorDark: "#2C2D3C",
			colorLight: "#FFFFFF",
			correctLevel: window.QRCode.CorrectLevel.M
		}));
	} catch (e) {
		console.error("Error al generar URL o código QR:", e);
	}
}
function Wg(e) {
	Q.pasoActual = e;
	for (let t = 1; t <= 4; t++) {
		let n = document.getElementById(`stepIndicator${t}`), r = document.getElementById(`stepPanel${t}`);
		n && (n.classList.toggle("active", t === e), n.classList.toggle("done", t < e)), r && (r.classList.toggle("active", t === e), r.style.display = t === e ? "block" : "none");
	}
	e === 4 && Ug(), window.scrollTo({
		top: 0,
		behavior: "smooth"
	});
}
function Gg(e) {
	Q.rolSeleccionado = e;
	let t = document.getElementById("cardRoleMute"), n = document.getElementById("cardRoleClient");
	t && t.classList.toggle("selected", e === "CLIENT_MUTE"), n && n.classList.toggle("selected", e === "CLIENT");
	let r = document.getElementById("posicionSelect");
	r && (r.value = e === "CLIENT_MUTE" ? "21600" : "259200"), Ug();
}
function Kg() {
	let { yamlText: e } = Rg(), t = new Blob([e], { type: "text/yaml;charset=utf-8" }), n = URL.createObjectURL(t), r = document.createElement("a");
	r.href = n, r.download = "andalucia-mesh-sfnarrow.yaml", document.body.appendChild(r), r.click(), r.remove(), URL.revokeObjectURL(n), $("Archivo andalucia-mesh-sfnarrow.yaml descargado con éxito.");
}
function qg() {
	let e = document.getElementById("qrShareUrl");
	e && e.value && navigator.clipboard.writeText(e.value).then(() => {
		alert("Enlace oficial de Meshtastic copiado al portapapeles."), $("Enlace de canales copiado al portapapeles.");
	});
}
function Jg() {
	let { configDoc: e } = Rg(), t = e.config.lora, n = e.config.device, r = e.config.position, i = n.role === "CLIENT_MUTE", a = Number(document.getElementById("telemetriaSelect")?.value || 0), o = [
		"# Configuración oficial Andalucía Mesh (SFNarrow)",
		`meshtastic --set-owner "${e.owner}" --set-owner-short "${e.owner_short}"`,
		`meshtastic --set lora.region ${t.region} --set lora.use_preset false`,
		`meshtastic --set lora.bandwidth ${t.bandwidth} --set lora.spread_factor ${t.spreadFactor} --set lora.coding_rate ${t.codingRate}`,
		`meshtastic --set lora.channel_num ${t.channelNum} --set lora.hop_limit ${t.hopLimit} --set lora.tx_power ${t.txPower}`,
		`meshtastic --set device.role ${n.role} --set device.node_info_broadcast_secs ${n.nodeInfoBroadcastSecs} --set device.disable_triple_click true --set device.tzdef "${n.tzdef}"`,
		`meshtastic --set position.position_broadcast_smart_enabled false --set position.position_broadcast_secs ${r.positionBroadcastSecs} --set position.fixed_position ${r.fixedPosition || !1}`,
		`meshtastic --set telemetry.device_update_interval ${a}`,
		`meshtastic --ch-set name "SFNarrow" --ch-set psk "AQ==" --ch-set uplink_enabled true --ch-set downlink_enabled ${!i} --ch-index 0`
	];
	if (e.channels && e.channels.length > 1) for (let t = 1; t < e.channels.length; t++) {
		let n = e.channels[t];
		n && n.settings && n.settings.name && o.push(`meshtastic --ch-set name "${n.settings.name}" --ch-set psk "${n.settings.psk || "AQ=="}" --ch-set uplink_enabled true --ch-index ${t}`);
	}
	if (e.module_config?.mqtt?.enabled) {
		let t = e.module_config.mqtt;
		o.push(`meshtastic --set mqtt.enabled true --set mqtt.address "${t.address}" --set mqtt.username "${t.username}" --set mqtt.password "${t.password}" --set mqtt.root "${t.root}" --set mqtt.encryption_enabled true --set mqtt.tls_enabled true`), o.push("meshtastic --set lora.ignore_mqtt true"), t.mapReportingEnabled && (o.push("meshtastic --set mqtt.map_reporting_enabled true --set mqtt.map_report_settings.publish_interval_secs 259200 --set mqtt.map_report_settings.position_precision 14 --set mqtt.map_report_settings.should_report_location true"), o.push("meshtastic --set lora.config_ok_to_mqtt true"));
	}
	let s = o.join("\n");
	navigator.clipboard.writeText(s).then(() => {
		alert("Comandos CLI de Meshtastic copiados al portapapeles."), $("Comandos CLI copiados al portapapeles.");
	});
}
function Yg(e) {
	Q.nodoConectado = e;
	let t = document.getElementById("statusPill");
	t && (t.textContent = e ? "⚡ Conectado" : "🔌 Desconectado", t.style.background = e ? "var(--color-correcto-fondo)" : "", t.style.color = e ? "var(--color-correcto-texto)" : "");
	let n = document.getElementById("workbenchStatusPill");
	n && (n.textContent = e ? "⚡ Conectado" : "🔌 Desconectado", n.style.background = e ? "var(--color-correcto-fondo)" : "", n.style.color = e ? "var(--color-correcto-texto)" : "");
	let r = document.getElementById("btnConnectDirect"), i = document.getElementById("btnDisconnectDirect");
	r && (r.disabled = e), i && (i.disabled = !e);
	let a = document.getElementById("btnConnectWorkbench"), o = document.getElementById("btnDisconnectWorkbench"), s = document.getElementById("btnDownloadLive"), c = document.getElementById("btnDownloadLiveHeader"), l = document.getElementById("btnUploadConfig");
	a && (a.disabled = e), o && (o.disabled = !e), s && (s.disabled = !e), c && (c.disabled = !e), l && (l.disabled = !e);
}
function Xg() {
	let e = Array.from(Q.channelMap.values()).sort((e, t) => (e.index ?? 0) - (t.index ?? 0)), t = Q.myNodeNum === null ? null : Ig.get(Q.myNodeNum), n = Q.ownerName || t?.longName || "Nodo Meshtastic", r = Q.ownerShort || t?.shortName || "MESH", i = Q.isUnmessagable === void 0 ? !!t?.isUnmessagable : Q.isUnmessagable, { config: a, moduleConfig: o, channels: s } = Lg(Q.configSections, Q.moduleConfigSections, e), c = {
		owner: n,
		owner_short: r,
		is_unmessagable: i,
		config: a,
		module_config: o,
		channels: s
	};
	Q.liveConfig = c;
	try {
		let e = Fg(c, {
			lineWidth: 120,
			noRefs: !0,
			sortKeys: !1
		}), t = document.getElementById("liveYamlTextarea");
		t && (t.value = e), Zg();
	} catch (e) {
		console.warn("Error serializando live config a YAML:", e);
	}
}
function Zg() {
	let e = document.getElementById("liveYamlTextarea")?.value.trim() || "", t = document.getElementById("desiredYamlTextarea")?.value.trim() || "", n = document.getElementById("diffBadge"), r = document.getElementById("diffOutputContainer");
	if (!n || !r) return;
	if (!e) {
		n.textContent = "Sin comparación activa", n.className = "badge-tag", n.style.background = "", n.style.color = "", r.innerHTML = "<em>Conéctate a tu nodo y lee su configuración para ver las diferencias exactas respecto al estándar SFNarrow.</em>";
		return;
	}
	if (e === t) {
		n.textContent = "✓ Idéntica (Sin cambios)", n.className = "badge-tag", n.style.background = "var(--color-correcto-fondo)", n.style.color = "var(--color-correcto-texto)", r.innerHTML = "<div style=\"color: var(--color-correcto-texto); padding: 0.5rem 0;\">✓ La configuración actual del nodo coincide con la configuración deseada.</div>";
		return;
	}
	let i = e.split("\n"), a = t.split("\n"), o = "<div style=\"font-family: var(--fuente-mono); font-size: 0.85rem; line-height: 1.5; max-height: 320px; overflow-y: auto; background: var(--color-superficie-sutil); border: 1px solid var(--color-borde); border-radius: var(--radio-sm); padding: 0.75rem;\">", s = 0, c = Math.max(i.length, a.length);
	for (let e = 0; e < c; e++) {
		let t = i[e], n = a[e];
		t === n ? o += `<div style="color: var(--color-texto-2); padding: 1px 4px;">  ${Qg(t || "")}</div>` : (s++, t !== void 0 && (o += `<div style="background: rgba(220, 38, 38, 0.15); color: #ef4444; padding: 1px 4px; border-radius: 2px;">- ${Qg(t)}</div>`), n !== void 0 && (o += `<div style="background: rgba(22, 163, 74, 0.15); color: #22c55e; padding: 1px 4px; border-radius: 2px;">+ ${Qg(n)}</div>`));
	}
	o += "</div>", n.textContent = `⚠️ ${s} diferencia${s > 1 ? "s" : ""}`, n.className = "badge-tag badge-tag-aviso", n.style.background = "", n.style.color = "", r.innerHTML = o;
}
function Qg(e) {
	return String(e).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
}
async function $g(e = "assistant") {
	let t = e === "workbench" || Q.modo === "workbench", n = t ? "transportSelectWorkbench" : "transportSelect", r = document.getElementById(n)?.value || "serial";
	if (r === "serial" && !("serial" in navigator)) {
		let e = "Web Serial API no está soportada en este navegador. Para conectar directamente por cable USB, utiliza Google Chrome, Microsoft Edge, Brave u Opera en tu ordenador.";
		alert(e), $(`Error de compatibilidad: ${e}`);
		return;
	}
	if (r === "bluetooth" && !("bluetooth" in navigator)) {
		let e = "Web Bluetooth API no está soportada en este navegador. Utiliza Google Chrome o Microsoft Edge.";
		alert(e), $(`Error de compatibilidad: ${e}`);
		return;
	}
	let i = document.getElementById(t ? "btnConnectWorkbench" : "btnConnectDirect");
	i && (i.disabled = !0);
	try {
		$(`Iniciando conexión directa por ${r.toUpperCase()}...`);
		let e;
		if (r === "serial") e = await Id.create(115200);
		else if (r === "bluetooth") e = await Rd.create();
		else if (r === "http") {
			let n = t ? "httpIpInputWorkbench" : "httpIpInput";
			e = new Ud(document.getElementById(n)?.value.trim() || "meshtastic.local", !1);
		}
		Q.transporte = e;
		let n = new Fd(e);
		Q.dispositivo = n, Q.nodoConectado = !0, Ig.clear(), Q.myNodeNum = null, Q.myNodeInfo = null, Q.ownerName = "", Q.ownerShort = "", Q.isUnmessagable = !1, Q.configSections = {}, Q.moduleConfigSections = {}, Q.channelMap.clear(), n.events.onDeviceStatus.subscribe((e) => {
			$(`Estado del enlace local: ${e}`), (e === 2 || e === "disconnected" || e === "DeviceDisconnected") && Yg(!1);
		}), n.events.onMyNodeInfo.subscribe((e) => {
			if (e) {
				if (Q.myNodeNum = e.myNodeNum >>> 0, Q.myNodeInfo = e, $(`Nodo local identificado: !${Q.myNodeNum.toString(16).padStart(8, "0")}`), Ig.has(Q.myNodeNum)) {
					let e = Ig.get(Q.myNodeNum);
					e?.longName && (Q.ownerName = e.longName), e?.shortName && (Q.ownerShort = e.shortName), e?.isUnmessagable !== void 0 && (Q.isUnmessagable = !!e.isUnmessagable);
				}
				Yg(!0), Xg();
			}
		}), n.events.onNodeInfoPacket.subscribe((e) => {
			if (!e) return;
			let t = e.num === void 0 ? null : e.num >>> 0;
			t !== null && e.user && (Ig.set(t, e.user), Q.myNodeNum !== null && t === Q.myNodeNum && (e.user.longName && (Q.ownerName = e.user.longName), e.user.shortName && (Q.ownerShort = e.user.shortName), e.user.isUnmessagable !== void 0 && (Q.isUnmessagable = !!e.user.isUnmessagable), $(`Identidad del nodo propio confirmada: ${Q.ownerName} (${Q.ownerShort})`), Xg()));
		}), n.events.onUserPacket.subscribe((e) => {
			if (!e?.data) return;
			let t = e.from === void 0 ? null : e.from >>> 0;
			(t === 0 || Q.myNodeNum !== null && t === Q.myNodeNum || t === null && Q.myNodeNum === null) && (e.data.longName && (Q.ownerName = e.data.longName), e.data.shortName && (Q.ownerShort = e.data.shortName), e.data.isUnmessagable !== void 0 && (Q.isUnmessagable = !!e.data.isUnmessagable), Xg());
		}), n.events.onConfigPacket.subscribe((e) => {
			if (e?.payloadVariant?.case && e.payloadVariant.value) {
				let t = e.payloadVariant.case;
				try {
					let n = dn(D.Config.ConfigSchema, e);
					n && n[t] && (Q.configSections[t] = n[t]);
				} catch (e) {
					console.warn(`Error parseando config.${t}:`, e);
				}
				Xg();
			}
		}), n.events.onModuleConfigPacket.subscribe((e) => {
			if (e?.payloadVariant?.case && e.payloadVariant.value) {
				let t = e.payloadVariant.case;
				try {
					let n = dn(D.ModuleConfig.ModuleConfigSchema, e);
					n && n[t] && (Q.moduleConfigSections[t] = n[t]);
				} catch (e) {
					console.warn(`Error parseando module_config.${t}:`, e);
				}
				Xg();
			}
		}), n.events.onChannelPacket.subscribe((e) => {
			if (e) {
				try {
					let t = dn(D.Channel.ChannelSchema, e);
					t && t.index !== void 0 && Q.channelMap.set(t.index, t);
				} catch (e) {
					console.warn("Error parseando channel:", e);
				}
				Xg();
			}
		}), Yg(!0), $("✓ Conexión establecida con éxito con el nodo Meshtastic."), $("Solicitando configuración al dispositivo...");
		try {
			await n.configure();
		} catch (e) {
			console.warn("Aviso en device.configure():", e);
		}
		try {
			await n.getOwner();
		} catch {}
		for (let e = 0; e < 8; e++) try {
			await n.getChannel(e);
		} catch {}
		alert("¡Nodo conectado con éxito! Leyendo parámetros del dispositivo...");
	} catch (e) {
		Yg(!1), $(`Error de conexión: ${e.message}`), alert(`No se pudo conectar al dispositivo: ${e.message}`);
	}
}
async function e_() {
	if (Q.dispositivo || Q.transporte) try {
		Q.dispositivo ? await Q.dispositivo.disconnect() : Q.transporte && await Q.transporte.disconnect();
	} catch (e) {
		$(`Aviso al desconectar: ${e.message}`);
	}
	Q.transporte = null, Q.dispositivo = null, Q.nodoConectado = !1, Yg(!1), $("Dispositivo desconectado.");
}
async function t_() {
	if (!Q.dispositivo || !Q.nodoConectado) alert("Debes conectar tu nodo primero por cable USB Serial o Bluetooth para leer su configuración."), $("Intento de lectura sin dispositivo conectado.");
	else {
		$("Solicitando parámetros actualizados al dispositivo...");
		try {
			await Q.dispositivo.configure(), await Q.dispositivo.getOwner();
			for (let e = 0; e < 8; e++) await Q.dispositivo.getChannel(e);
			if (Q.myNodeNum !== null && Ig.has(Q.myNodeNum)) {
				let e = Ig.get(Q.myNodeNum);
				e?.longName && (Q.ownerName = e.longName), e?.shortName && (Q.ownerShort = e.shortName), e?.isUnmessagable !== void 0 && (Q.isUnmessagable = !!e.isUnmessagable);
			}
			Xg(), $("Configuración leída y volcada en el panel actual.");
		} catch (e) {
			$(`Error solicitando configuración: ${e.message}`), alert(`Error al leer del nodo: ${e.message}`);
		}
	}
}
function n_() {
	let e = document.getElementById("liveYamlTextarea")?.value, t = document.getElementById("desiredYamlTextarea");
	e && t && (t.value = e, $("Configuración leída copiada a panel deseado."), Zg());
}
async function r_() {
	if (!Q.dispositivo || !Q.nodoConectado) {
		alert("Conecta tu nodo por cable USB Serial o Bluetooth para volcar los cambios.");
		return;
	}
	let e = document.getElementById("desiredYamlTextarea")?.value.trim();
	if (!e) {
		alert("No hay configuración deseada para escribir en el nodo.");
		return;
	}
	let t;
	try {
		t = Xm(e);
	} catch (e) {
		alert(`Error de formato en el YAML deseado: ${e.message}`);
		return;
	}
	let n = document.getElementById("btnUploadConfig");
	n && (n.disabled = !0);
	try {
		if ($("Escribiendo configuración deseada en el nodo..."), t.owner || t.owner_short) {
			let e = m(D.Mesh.UserSchema, {
				longName: t.owner || "MiNodo-Andalucia",
				shortName: (t.owner_short || "AND1").slice(0, 4)
			});
			await Q.dispositivo.setOwner(e), $("✓ Identidad (Owner) actualizada.");
		}
		if (t.config?.device) {
			let e = m(D.Config.Config_DeviceConfigSchema, {
				role: +(t.config.device.role === "CLIENT_MUTE"),
				nodeInfoBroadcastSecs: t.config.device.nodeInfoBroadcastSecs || 259200,
				disableTripleClick: !!t.config.device.disableTripleClick,
				tzdef: t.config.device.tzdef || "GMT-1GMT,M3.5.0,M10.5.0/3"
			}), n = m(D.Config.ConfigSchema, { payloadVariant: {
				case: "device",
				value: e
			} });
			await Q.dispositivo.setConfig(n), $("✓ Parámetros de Dispositivo (Role / NodeInfo / TZ / Botón) enviados.");
		}
		if (t.config?.lora) {
			let e = m(D.Config.Config_LoRaConfigSchema, {
				region: 3,
				usePreset: !!t.config.lora.usePreset,
				bandwidth: Number(t.config.lora.bandwidth) || 62,
				spreadFactor: Number(t.config.lora.spreadFactor) || 7,
				codingRate: Number(t.config.lora.codingRate) || 5,
				channelNum: Number(t.config.lora.channelNum) || 4,
				hopLimit: Number(t.config.lora.hopLimit) || 4,
				txPower: Number(t.config.lora.txPower) || 27,
				txEnabled: !0,
				sx126xRxBoostedGain: !0,
				ignoreMqtt: !!t.config.lora.ignoreMqtt,
				configOkToMqtt: !!t.config.lora.configOkToMqtt
			}), n = m(D.Config.ConfigSchema, { payloadVariant: {
				case: "lora",
				value: e
			} });
			await Q.dispositivo.setConfig(n), $("✓ Parámetros de Radio LoRa (SFNarrow EU_868) enviados.");
		}
		if (t.config?.position) {
			let e = m(D.Config.Config_PositionConfigSchema, {
				positionBroadcastSmartEnabled: !!t.config.position.positionBroadcastSmartEnabled,
				positionBroadcastSecs: Number(t.config.position.positionBroadcastSecs) || 21600,
				positionFlags: Number(t.config.position.positionFlags) || 0,
				fixedPosition: !!t.config.position.fixedPosition
			}), n = m(D.Config.ConfigSchema, { payloadVariant: {
				case: "position",
				value: e
			} });
			await Q.dispositivo.setConfig(n), $("✓ Parámetros de Posición enviados.");
		}
		if (t.module_config?.telemetry) {
			let e = m(D.ModuleConfig.ModuleConfig_TelemetryConfigSchema, { deviceUpdateInterval: Number(t.module_config.telemetry.deviceUpdateInterval) || 0 }), n = m(D.ModuleConfig.ModuleConfigSchema, { payloadVariant: {
				case: "telemetry",
				value: e
			} });
			await Q.dispositivo.setModuleConfig(n), $("✓ Módulo de Telemetría enviado.");
		}
		if (t.module_config?.mqtt) {
			let e = {
				enabled: !!t.module_config.mqtt.enabled,
				address: t.module_config.mqtt.address || "mqtt.desdechipiona.es",
				username: t.module_config.mqtt.username || "meshdev",
				password: t.module_config.mqtt.password || "large4cats",
				root: t.module_config.mqtt.root || "msh/EU_868",
				encryptionEnabled: !0,
				tlsEnabled: !0,
				mapReportingEnabled: !!t.module_config.mqtt.mapReportingEnabled
			};
			t.module_config.mqtt.mapReportSettings && (e.mapReportSettings = m(D.ModuleConfig.ModuleConfig_MapReportSettingsSchema, {
				publishIntervalSecs: Number(t.module_config.mqtt.mapReportSettings.publishIntervalSecs) || 259200,
				positionPrecision: Number(t.module_config.mqtt.mapReportSettings.positionPrecision) || 14,
				shouldReportLocation: !!t.module_config.mqtt.mapReportSettings.shouldReportLocation
			}));
			let n = m(D.ModuleConfig.ModuleConfig_MQTTConfigSchema, e), r = m(D.ModuleConfig.ModuleConfigSchema, { payloadVariant: {
				case: "mqtt",
				value: n
			} });
			await Q.dispositivo.setModuleConfig(r), $("✓ Módulo MQTT comunitario y reporte en mapa enviados.");
		}
		if (Array.isArray(t.channels)) {
			for (let e of t.channels) if (e && e.settings && e.settings.name) {
				let t = m(D.Channel.ChannelSchema, {
					index: e.index,
					role: e.role === "PRIMARY" ? 1 : 2,
					settings: {
						name: e.settings.name,
						psk: new Uint8Array([1]),
						uplinkEnabled: e.settings.uplinkEnabled ?? !0,
						downlinkEnabled: e.settings.downlinkEnabled ?? !0,
						moduleSettings: e.settings.moduleSettings ? { positionPrecision: e.settings.moduleSettings.positionPrecision ?? 15 } : void 0
					}
				});
				await Q.dispositivo.setChannel(t), $(`✓ Canal ${e.index} (${e.settings.name}) actualizado.`);
			}
		}
		$("¡Configuración escrita con éxito en el nodo!"), alert("¡Configuración volcada con éxito al dispositivo! El nodo se reiniciará con los nuevos ajustes.");
	} catch (e) {
		$(`Error al escribir configuración: ${e.message}`), alert(`Error al escribir en el nodo: ${e.message}`);
	} finally {
		n && (n.disabled = !1);
	}
}
function i_() {
	document.getElementById("desiredYamlTextarea")?.value && (Q.desiredConfig = null), Zg();
}
function a_(e) {
	Q.modo = e;
	let t = document.getElementById("tabAssistantMode"), n = document.getElementById("tabWorkbenchMode"), r = document.getElementById("viewAssistant"), i = document.getElementById("viewWorkbench"), a = e === "asistente";
	t && t.classList.toggle("active", a), n && n.classList.toggle("active", !a), r && (r.style.display = a ? "block" : "none"), i && (i.style.display = a ? "none" : "block"), a || (Rg(), Zg());
}
function o_() {
	let e = document.documentElement, t = (e.getAttribute("data-theme") || e.getAttribute("data-tema") || "dark") === "light" ? "dark" : "light";
	e.setAttribute("data-theme", t), e.setAttribute("data-tema", t);
	try {
		localStorage.setItem("snm_theme", t), localStorage.setItem("snm_tema", t);
	} catch {}
	let n = document.getElementById("themeIcon");
	n && (n.textContent = t === "light" ? "🌙" : "☀️");
}
window.irAlPaso = Wg, window.seleccionarRol = Gg, window.actualizarConfiguracion = Ug, window.descargarYamlDeseado = Kg, window.copiarEnlaceQR = qg, window.copiarComandosCli = Jg, window.conectarDispositivo = $g, window.desconectarDispositivo = e_, window.descargarConfiguracionNodo = t_, window.copiarLiveADeseado = n_, window.aplicarDeseadoANodo = r_, window.alEditarYamlDeseado = i_, window.actualizarDiff = Zg, window.setModo = a_, window.toggleTema = o_, window.limpiarLog = () => {
	let e = document.getElementById("logTextarea");
	e && (e.value = "");
}, document.addEventListener("click", (e) => {
	let t = e.target;
	if (!t) return;
	let n = t.closest("#tabAssistantMode, #tabWorkbenchMode");
	n && (n.id === "tabAssistantMode" && a_("asistente"), n.id === "tabWorkbenchMode" && a_("workbench"));
});
function s_() {
	let e = document.documentElement.getAttribute("data-theme") || localStorage.getItem("snm_theme") || localStorage.getItem("snm_tema") || "dark";
	document.documentElement.setAttribute("data-theme", e), document.documentElement.setAttribute("data-tema", e);
	let t = document.getElementById("themeIcon");
	t && (t.textContent = e === "light" ? "🌙" : "☀️"), new MutationObserver((e) => {
		for (let n of e) if (n.type === "attributes" && (n.attributeName === "data-theme" || n.attributeName === "data-tema")) {
			let e = document.documentElement.getAttribute("data-theme") || document.documentElement.getAttribute("data-tema") || "dark";
			t && (t.textContent = e === "light" ? "🌙" : "☀️");
		}
	}).observe(document.documentElement, {
		attributes: !0,
		attributeFilter: ["data-theme", "data-tema"]
	}), document.getElementById("themeToggleBtn")?.addEventListener("click", o_), document.getElementById("tabAssistantMode")?.addEventListener("click", () => a_("asistente")), document.getElementById("tabWorkbenchMode")?.addEventListener("click", () => a_("workbench")), document.getElementById("transportSelect")?.addEventListener("change", (e) => {
		let t = e.target.value === "http", n = document.getElementById("httpIpGroup");
		n && (n.style.display = t ? "flex" : "none");
	}), document.getElementById("transportSelectWorkbench")?.addEventListener("change", (e) => {
		let t = e.target.value === "http", n = document.getElementById("httpIpGroupWorkbench");
		n && (n.style.display = t ? "flex" : "none");
	}), document.getElementById("chkMqtt")?.addEventListener("change", Ug), document.getElementById("chkMqttMap")?.addEventListener("change", Ug), Ug(), $("Configurador de Andalucía Mesh iniciado con preset SFNarrow.");
}
document.readyState === "loading" ? document.addEventListener("DOMContentLoaded", s_) : s_();
//#endregion
export { Ug as actualizarConfiguracion, Zg as actualizarDiff, i_ as alEditarYamlDeseado, r_ as aplicarDeseadoANodo, $g as conectarDispositivo, Rg as construirYamlDeseado, Jg as copiarComandosCli, qg as copiarEnlaceQR, n_ as copiarLiveADeseado, t_ as descargarConfiguracionNodo, Kg as descargarYamlDeseado, e_ as desconectarDispositivo, Wg as irAlPaso, Gg as seleccionarRol, a_ as setModo, o_ as toggleTema };
