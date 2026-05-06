--
-- PostgreSQL database dump
--

\restrict VYMcMt64lnfBswVFo5gEttghPd9fX5iNUmL1mKHFJmWiFX4ctIR7UZ5t1Z8Jmnq

-- Dumped from database version 15.17 (Debian 15.17-1.pgdg13+1)
-- Dumped by pg_dump version 15.17 (Debian 15.17-1.pgdg13+1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: areas; Type: TABLE; Schema: public; Owner: laravel_user
--

CREATE TABLE public.areas (
    id bigint NOT NULL,
    codigo character varying(255) NOT NULL,
    nombre character varying(255) NOT NULL,
    estatus boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.areas OWNER TO laravel_user;

--
-- Name: areas_id_seq; Type: SEQUENCE; Schema: public; Owner: laravel_user
--

CREATE SEQUENCE public.areas_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.areas_id_seq OWNER TO laravel_user;

--
-- Name: areas_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: laravel_user
--

ALTER SEQUENCE public.areas_id_seq OWNED BY public.areas.id;


--
-- Name: cache; Type: TABLE; Schema: public; Owner: laravel_user
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache OWNER TO laravel_user;

--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: laravel_user
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache_locks OWNER TO laravel_user;

--
-- Name: centros_costos; Type: TABLE; Schema: public; Owner: laravel_user
--

CREATE TABLE public.centros_costos (
    id bigint NOT NULL,
    codigo character varying(255) NOT NULL,
    nombre character varying(255) NOT NULL,
    cuenta_contable character varying(255) NOT NULL,
    estatus boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.centros_costos OWNER TO laravel_user;

--
-- Name: centros_costos_id_seq; Type: SEQUENCE; Schema: public; Owner: laravel_user
--

CREATE SEQUENCE public.centros_costos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.centros_costos_id_seq OWNER TO laravel_user;

--
-- Name: centros_costos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: laravel_user
--

ALTER SEQUENCE public.centros_costos_id_seq OWNED BY public.centros_costos.id;


--
-- Name: concepto_rol; Type: TABLE; Schema: public; Owner: laravel_user
--

CREATE TABLE public.concepto_rol (
    concepto_id bigint NOT NULL,
    rol_id bigint NOT NULL
);


ALTER TABLE public.concepto_rol OWNER TO laravel_user;

--
-- Name: conceptos; Type: TABLE; Schema: public; Owner: laravel_user
--

CREATE TABLE public.conceptos (
    id bigint NOT NULL,
    codigo character varying(255) NOT NULL,
    nombre character varying(255) NOT NULL,
    categoria character varying(255),
    descripcion character varying(255),
    tipo_aplicacion character varying(20) DEFAULT 'Diario'::character varying NOT NULL,
    orden integer DEFAULT 0 NOT NULL,
    aplica_iva boolean DEFAULT true NOT NULL,
    tope_referencia numeric(10,2),
    vigencia_desde date,
    vigencia_hasta date,
    estatus boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.conceptos OWNER TO laravel_user;

--
-- Name: conceptos_id_seq; Type: SEQUENCE; Schema: public; Owner: laravel_user
--

CREATE SEQUENCE public.conceptos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.conceptos_id_seq OWNER TO laravel_user;

--
-- Name: conceptos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: laravel_user
--

ALTER SEQUENCE public.conceptos_id_seq OWNED BY public.conceptos.id;


--
-- Name: empleados; Type: TABLE; Schema: public; Owner: laravel_user
--

CREATE TABLE public.empleados (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    nombre_completo character varying(255) NOT NULL,
    must_change_password boolean DEFAULT true NOT NULL,
    puesto character varying(255),
    area_id bigint,
    centro_costo_id bigint,
    rfc character varying(13),
    curp character varying(18),
    numero_nomina character varying(255),
    banco_nomina character varying(255),
    cuenta_nomina character varying(255),
    clabe_nomina character varying(255),
    nss character varying(255),
    fecha_ingreso date,
    telefono character varying(255),
    tarjeta_credito_corporativa_asignada boolean DEFAULT false NOT NULL,
    limite_credito_tarjeta numeric(10,2),
    estatus boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.empleados OWNER TO laravel_user;

--
-- Name: empleados_id_seq; Type: SEQUENCE; Schema: public; Owner: laravel_user
--

CREATE SEQUENCE public.empleados_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.empleados_id_seq OWNER TO laravel_user;

--
-- Name: empleados_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: laravel_user
--

ALTER SEQUENCE public.empleados_id_seq OWNED BY public.empleados.id;


--
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: laravel_user
--

CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection text NOT NULL,
    queue text NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.failed_jobs OWNER TO laravel_user;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: laravel_user
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.failed_jobs_id_seq OWNER TO laravel_user;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: laravel_user
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- Name: flujos_aprobacion; Type: TABLE; Schema: public; Owner: laravel_user
--

CREATE TABLE public.flujos_aprobacion (
    id bigint NOT NULL,
    tipo_solicitud character varying(255) DEFAULT 'viaticos'::character varying NOT NULL,
    role_id bigint NOT NULL,
    orden integer DEFAULT 1 NOT NULL,
    requerido boolean DEFAULT false NOT NULL,
    minimo_aprobaciones integer DEFAULT 2 NOT NULL,
    estatus boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.flujos_aprobacion OWNER TO laravel_user;

--
-- Name: flujos_aprobacion_id_seq; Type: SEQUENCE; Schema: public; Owner: laravel_user
--

CREATE SEQUENCE public.flujos_aprobacion_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.flujos_aprobacion_id_seq OWNER TO laravel_user;

--
-- Name: flujos_aprobacion_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: laravel_user
--

ALTER SEQUENCE public.flujos_aprobacion_id_seq OWNED BY public.flujos_aprobacion.id;


--
-- Name: folio_counters; Type: TABLE; Schema: public; Owner: laravel_user
--

CREATE TABLE public.folio_counters (
    id bigint NOT NULL,
    prefix character varying(255) NOT NULL,
    year integer NOT NULL,
    current bigint DEFAULT '0'::bigint NOT NULL
);


ALTER TABLE public.folio_counters OWNER TO laravel_user;

--
-- Name: folio_counters_id_seq; Type: SEQUENCE; Schema: public; Owner: laravel_user
--

CREATE SEQUENCE public.folio_counters_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.folio_counters_id_seq OWNER TO laravel_user;

--
-- Name: folio_counters_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: laravel_user
--

ALTER SEQUENCE public.folio_counters_id_seq OWNED BY public.folio_counters.id;


--
-- Name: gasto_comprobantes; Type: TABLE; Schema: public; Owner: laravel_user
--

CREATE TABLE public.gasto_comprobantes (
    id bigint NOT NULL,
    gasto_id bigint NOT NULL,
    archivo character varying(255) NOT NULL,
    tipo character varying(255),
    uuid character varying(255),
    validacion_manual character varying(255) DEFAULT 'pendiente'::character varying NOT NULL,
    validado_por bigint,
    monto numeric(12,2),
    subido_por bigint NOT NULL,
    fecha_subida timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    sat_status character varying(255),
    sat_checked_at timestamp(0) without time zone,
    sat_attempts integer DEFAULT 0 NOT NULL,
    meta_cfdi json,
    sat_last_error text,
    fecha_gasto date,
    comentario_validacion text,
    validado_en timestamp(0) without time zone,
    archivo_pdf character varying(255)
);


ALTER TABLE public.gasto_comprobantes OWNER TO laravel_user;

--
-- Name: gasto_comprobantes_id_seq; Type: SEQUENCE; Schema: public; Owner: laravel_user
--

CREATE SEQUENCE public.gasto_comprobantes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.gasto_comprobantes_id_seq OWNER TO laravel_user;

--
-- Name: gasto_comprobantes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: laravel_user
--

ALTER SEQUENCE public.gasto_comprobantes_id_seq OWNED BY public.gasto_comprobantes.id;


--
-- Name: gastos; Type: TABLE; Schema: public; Owner: laravel_user
--

CREATE TABLE public.gastos (
    id bigint NOT NULL,
    solicitud_id bigint NOT NULL,
    concepto_id bigint NOT NULL,
    fecha_gasto date NOT NULL,
    monto numeric(12,2) NOT NULL,
    rfc_proveedor character varying(15),
    uuid_factura uuid,
    archivo_xml character varying(255),
    archivo_pdf character varying(255),
    estatus character varying(255) DEFAULT 'Validado'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.gastos OWNER TO laravel_user;

--
-- Name: gastos_auditoria; Type: TABLE; Schema: public; Owner: laravel_user
--

CREATE TABLE public.gastos_auditoria (
    id bigint NOT NULL,
    gasto_id bigint,
    excepcion_id bigint,
    evento character varying(255) NOT NULL,
    actor_id bigint,
    origen character varying(255) DEFAULT 'sistema'::character varying NOT NULL,
    datos_antes json,
    datos_despues json,
    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.gastos_auditoria OWNER TO laravel_user;

--
-- Name: gastos_auditoria_id_seq; Type: SEQUENCE; Schema: public; Owner: laravel_user
--

CREATE SEQUENCE public.gastos_auditoria_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.gastos_auditoria_id_seq OWNER TO laravel_user;

--
-- Name: gastos_auditoria_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: laravel_user
--

ALTER SEQUENCE public.gastos_auditoria_id_seq OWNED BY public.gastos_auditoria.id;


--
-- Name: gastos_excepciones; Type: TABLE; Schema: public; Owner: laravel_user
--

CREATE TABLE public.gastos_excepciones (
    id bigint NOT NULL,
    gasto_id bigint NOT NULL,
    nivel integer NOT NULL,
    estatus character varying(255) DEFAULT 'pendiente'::character varying NOT NULL,
    comentario text,
    aprobado_por bigint,
    resuelto_en timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.gastos_excepciones OWNER TO laravel_user;

--
-- Name: gastos_excepciones_id_seq; Type: SEQUENCE; Schema: public; Owner: laravel_user
--

CREATE SEQUENCE public.gastos_excepciones_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.gastos_excepciones_id_seq OWNER TO laravel_user;

--
-- Name: gastos_excepciones_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: laravel_user
--

ALTER SEQUENCE public.gastos_excepciones_id_seq OWNED BY public.gastos_excepciones.id;


--
-- Name: gastos_id_seq; Type: SEQUENCE; Schema: public; Owner: laravel_user
--

CREATE SEQUENCE public.gastos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.gastos_id_seq OWNER TO laravel_user;

--
-- Name: gastos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: laravel_user
--

ALTER SEQUENCE public.gastos_id_seq OWNED BY public.gastos.id;


--
-- Name: job_batches; Type: TABLE; Schema: public; Owner: laravel_user
--

CREATE TABLE public.job_batches (
    id character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    total_jobs integer NOT NULL,
    pending_jobs integer NOT NULL,
    failed_jobs integer NOT NULL,
    failed_job_ids text NOT NULL,
    options text,
    cancelled_at integer,
    created_at integer NOT NULL,
    finished_at integer
);


ALTER TABLE public.job_batches OWNER TO laravel_user;

--
-- Name: jobs; Type: TABLE; Schema: public; Owner: laravel_user
--

CREATE TABLE public.jobs (
    id bigint NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer,
    available_at integer NOT NULL,
    created_at integer NOT NULL
);


ALTER TABLE public.jobs OWNER TO laravel_user;

--
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: laravel_user
--

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.jobs_id_seq OWNER TO laravel_user;

--
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: laravel_user
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: laravel_user
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


ALTER TABLE public.migrations OWNER TO laravel_user;

--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: laravel_user
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.migrations_id_seq OWNER TO laravel_user;

--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: laravel_user
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: model_has_permissions; Type: TABLE; Schema: public; Owner: laravel_user
--

CREATE TABLE public.model_has_permissions (
    permission_id bigint NOT NULL,
    model_type character varying(255) NOT NULL,
    model_id bigint NOT NULL
);


ALTER TABLE public.model_has_permissions OWNER TO laravel_user;

--
-- Name: model_has_roles; Type: TABLE; Schema: public; Owner: laravel_user
--

CREATE TABLE public.model_has_roles (
    role_id bigint NOT NULL,
    model_type character varying(255) NOT NULL,
    model_id bigint NOT NULL
);


ALTER TABLE public.model_has_roles OWNER TO laravel_user;

--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: laravel_user
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


ALTER TABLE public.password_reset_tokens OWNER TO laravel_user;

--
-- Name: permissions; Type: TABLE; Schema: public; Owner: laravel_user
--

CREATE TABLE public.permissions (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    guard_name character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.permissions OWNER TO laravel_user;

--
-- Name: permissions_id_seq; Type: SEQUENCE; Schema: public; Owner: laravel_user
--

CREATE SEQUENCE public.permissions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.permissions_id_seq OWNER TO laravel_user;

--
-- Name: permissions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: laravel_user
--

ALTER SEQUENCE public.permissions_id_seq OWNED BY public.permissions.id;


--
-- Name: politicas_gastos; Type: TABLE; Schema: public; Owner: laravel_user
--

CREATE TABLE public.politicas_gastos (
    id bigint NOT NULL,
    role_id bigint NOT NULL,
    concepto_id bigint NOT NULL,
    monto_max numeric(12,2) NOT NULL,
    tipo_limite character varying(20) DEFAULT 'Diario'::character varying NOT NULL,
    monto_libre numeric(12,2),
    monto_comprobante numeric(12,2),
    monto_factura numeric(12,2),
    valida_sat boolean DEFAULT false NOT NULL,
    acumulable_dia boolean DEFAULT true NOT NULL,
    permite_excepcion boolean DEFAULT false NOT NULL,
    vigencia_desde date,
    vigencia_hasta date,
    estatus boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.politicas_gastos OWNER TO laravel_user;

--
-- Name: politicas_gastos_auditoria; Type: TABLE; Schema: public; Owner: laravel_user
--

CREATE TABLE public.politicas_gastos_auditoria (
    id bigint NOT NULL,
    politica_id bigint,
    version_id bigint,
    evento character varying(30) NOT NULL,
    actor_id bigint,
    origen character varying(20) DEFAULT 'manual'::character varying NOT NULL,
    datos_antes jsonb,
    datos_despues jsonb,
    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.politicas_gastos_auditoria OWNER TO laravel_user;

--
-- Name: politicas_gastos_auditoria_id_seq; Type: SEQUENCE; Schema: public; Owner: laravel_user
--

CREATE SEQUENCE public.politicas_gastos_auditoria_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.politicas_gastos_auditoria_id_seq OWNER TO laravel_user;

--
-- Name: politicas_gastos_auditoria_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: laravel_user
--

ALTER SEQUENCE public.politicas_gastos_auditoria_id_seq OWNED BY public.politicas_gastos_auditoria.id;


--
-- Name: politicas_gastos_id_seq; Type: SEQUENCE; Schema: public; Owner: laravel_user
--

CREATE SEQUENCE public.politicas_gastos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.politicas_gastos_id_seq OWNER TO laravel_user;

--
-- Name: politicas_gastos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: laravel_user
--

ALTER SEQUENCE public.politicas_gastos_id_seq OWNED BY public.politicas_gastos.id;


--
-- Name: politicas_gastos_versiones; Type: TABLE; Schema: public; Owner: laravel_user
--

CREATE TABLE public.politicas_gastos_versiones (
    id bigint NOT NULL,
    politica_id bigint NOT NULL,
    role_id bigint NOT NULL,
    concepto_id bigint NOT NULL,
    monto_max numeric(12,2) NOT NULL,
    tipo_limite character varying(20) DEFAULT 'Diario'::character varying NOT NULL,
    monto_libre numeric(12,2),
    monto_comprobante numeric(12,2),
    monto_factura numeric(12,2),
    valida_sat boolean DEFAULT false NOT NULL,
    acumulable_dia boolean DEFAULT true NOT NULL,
    permite_excepcion boolean DEFAULT false NOT NULL,
    vigencia_desde date,
    vigencia_hasta date,
    motivo character varying(255),
    creado_por bigint,
    aprobado_por bigint,
    approved_at timestamp(0) without time zone,
    estatus character varying(20) DEFAULT 'Aprobada'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.politicas_gastos_versiones OWNER TO laravel_user;

--
-- Name: politicas_gastos_versiones_id_seq; Type: SEQUENCE; Schema: public; Owner: laravel_user
--

CREATE SEQUENCE public.politicas_gastos_versiones_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.politicas_gastos_versiones_id_seq OWNER TO laravel_user;

--
-- Name: politicas_gastos_versiones_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: laravel_user
--

ALTER SEQUENCE public.politicas_gastos_versiones_id_seq OWNED BY public.politicas_gastos_versiones.id;


--
-- Name: proyectos; Type: TABLE; Schema: public; Owner: laravel_user
--

CREATE TABLE public.proyectos (
    id bigint NOT NULL,
    codigo character varying(255) NOT NULL,
    nombre character varying(255) NOT NULL,
    cliente character varying(255),
    tipo character varying(255) DEFAULT 'Proyecto'::character varying NOT NULL,
    descripcion text,
    region character varying(255),
    prioridad character varying(255) DEFAULT 'Media'::character varying NOT NULL,
    estado_operativo character varying(255) DEFAULT 'Draft'::character varying NOT NULL,
    centro_costo_id bigint,
    responsable_id bigint,
    presupuesto_total numeric(12,2),
    fecha_inicio date,
    fecha_fin date,
    pais character varying(255),
    estado character varying(255),
    ciudad character varying(255),
    estatus boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.proyectos OWNER TO laravel_user;

--
-- Name: proyectos_id_seq; Type: SEQUENCE; Schema: public; Owner: laravel_user
--

CREATE SEQUENCE public.proyectos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.proyectos_id_seq OWNER TO laravel_user;

--
-- Name: proyectos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: laravel_user
--

ALTER SEQUENCE public.proyectos_id_seq OWNED BY public.proyectos.id;


--
-- Name: role_has_permissions; Type: TABLE; Schema: public; Owner: laravel_user
--

CREATE TABLE public.role_has_permissions (
    permission_id bigint NOT NULL,
    role_id bigint NOT NULL
);


ALTER TABLE public.role_has_permissions OWNER TO laravel_user;

--
-- Name: roles; Type: TABLE; Schema: public; Owner: laravel_user
--

CREATE TABLE public.roles (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    guard_name character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.roles OWNER TO laravel_user;

--
-- Name: roles_id_seq; Type: SEQUENCE; Schema: public; Owner: laravel_user
--

CREATE SEQUENCE public.roles_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.roles_id_seq OWNER TO laravel_user;

--
-- Name: roles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: laravel_user
--

ALTER SEQUENCE public.roles_id_seq OWNED BY public.roles.id;


--
-- Name: sessions; Type: TABLE; Schema: public; Owner: laravel_user
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


ALTER TABLE public.sessions OWNER TO laravel_user;

--
-- Name: solicitud_aprobaciones; Type: TABLE; Schema: public; Owner: laravel_user
--

CREATE TABLE public.solicitud_aprobaciones (
    id bigint NOT NULL,
    solicitud_id bigint NOT NULL,
    role_id bigint NOT NULL,
    user_id bigint NOT NULL,
    accion character varying(255) NOT NULL,
    comentario text,
    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.solicitud_aprobaciones OWNER TO laravel_user;

--
-- Name: solicitud_aprobaciones_id_seq; Type: SEQUENCE; Schema: public; Owner: laravel_user
--

CREATE SEQUENCE public.solicitud_aprobaciones_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.solicitud_aprobaciones_id_seq OWNER TO laravel_user;

--
-- Name: solicitud_aprobaciones_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: laravel_user
--

ALTER SEQUENCE public.solicitud_aprobaciones_id_seq OWNED BY public.solicitud_aprobaciones.id;


--
-- Name: solicitud_detalles; Type: TABLE; Schema: public; Owner: laravel_user
--

CREATE TABLE public.solicitud_detalles (
    id bigint NOT NULL,
    solicitud_id bigint NOT NULL,
    concepto_id bigint NOT NULL,
    monto_estimado numeric(12,2) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    justificacion_exceso text
);


ALTER TABLE public.solicitud_detalles OWNER TO laravel_user;

--
-- Name: solicitud_detalles_id_seq; Type: SEQUENCE; Schema: public; Owner: laravel_user
--

CREATE SEQUENCE public.solicitud_detalles_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.solicitud_detalles_id_seq OWNER TO laravel_user;

--
-- Name: solicitud_detalles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: laravel_user
--

ALTER SEQUENCE public.solicitud_detalles_id_seq OWNED BY public.solicitud_detalles.id;


--
-- Name: solicitudes; Type: TABLE; Schema: public; Owner: laravel_user
--

CREATE TABLE public.solicitudes (
    id bigint NOT NULL,
    folio character varying(255) NOT NULL,
    empleado_id bigint NOT NULL,
    area_id bigint,
    proyecto_id bigint,
    fecha_solicitud timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    fecha_inicio date,
    fecha_fin date,
    motivo text,
    monto_total numeric(12,2) DEFAULT '0'::numeric NOT NULL,
    motivo_rechazo text,
    estatus character varying(255) DEFAULT 'Borrador'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    motivo_cancelacion text
);


ALTER TABLE public.solicitudes OWNER TO laravel_user;

--
-- Name: solicitudes_auditoria; Type: TABLE; Schema: public; Owner: laravel_user
--

CREATE TABLE public.solicitudes_auditoria (
    id bigint NOT NULL,
    solicitud_id bigint NOT NULL,
    evento character varying(255) NOT NULL,
    actor_id bigint,
    datos json,
    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.solicitudes_auditoria OWNER TO laravel_user;

--
-- Name: solicitudes_auditoria_id_seq; Type: SEQUENCE; Schema: public; Owner: laravel_user
--

CREATE SEQUENCE public.solicitudes_auditoria_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.solicitudes_auditoria_id_seq OWNER TO laravel_user;

--
-- Name: solicitudes_auditoria_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: laravel_user
--

ALTER SEQUENCE public.solicitudes_auditoria_id_seq OWNED BY public.solicitudes_auditoria.id;


--
-- Name: solicitudes_id_seq; Type: SEQUENCE; Schema: public; Owner: laravel_user
--

CREATE SEQUENCE public.solicitudes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.solicitudes_id_seq OWNER TO laravel_user;

--
-- Name: solicitudes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: laravel_user
--

ALTER SEQUENCE public.solicitudes_id_seq OWNED BY public.solicitudes.id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: laravel_user
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    two_factor_secret text,
    two_factor_recovery_codes text,
    two_factor_confirmed_at timestamp(0) without time zone,
    must_change_password boolean DEFAULT true NOT NULL,
    active boolean DEFAULT true NOT NULL,
    blocked boolean DEFAULT false NOT NULL
);


ALTER TABLE public.users OWNER TO laravel_user;

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: laravel_user
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.users_id_seq OWNER TO laravel_user;

--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: laravel_user
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: areas id; Type: DEFAULT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.areas ALTER COLUMN id SET DEFAULT nextval('public.areas_id_seq'::regclass);


--
-- Name: centros_costos id; Type: DEFAULT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.centros_costos ALTER COLUMN id SET DEFAULT nextval('public.centros_costos_id_seq'::regclass);


--
-- Name: conceptos id; Type: DEFAULT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.conceptos ALTER COLUMN id SET DEFAULT nextval('public.conceptos_id_seq'::regclass);


--
-- Name: empleados id; Type: DEFAULT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.empleados ALTER COLUMN id SET DEFAULT nextval('public.empleados_id_seq'::regclass);


--
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- Name: flujos_aprobacion id; Type: DEFAULT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.flujos_aprobacion ALTER COLUMN id SET DEFAULT nextval('public.flujos_aprobacion_id_seq'::regclass);


--
-- Name: folio_counters id; Type: DEFAULT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.folio_counters ALTER COLUMN id SET DEFAULT nextval('public.folio_counters_id_seq'::regclass);


--
-- Name: gasto_comprobantes id; Type: DEFAULT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.gasto_comprobantes ALTER COLUMN id SET DEFAULT nextval('public.gasto_comprobantes_id_seq'::regclass);


--
-- Name: gastos id; Type: DEFAULT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.gastos ALTER COLUMN id SET DEFAULT nextval('public.gastos_id_seq'::regclass);


--
-- Name: gastos_auditoria id; Type: DEFAULT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.gastos_auditoria ALTER COLUMN id SET DEFAULT nextval('public.gastos_auditoria_id_seq'::regclass);


--
-- Name: gastos_excepciones id; Type: DEFAULT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.gastos_excepciones ALTER COLUMN id SET DEFAULT nextval('public.gastos_excepciones_id_seq'::regclass);


--
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: permissions id; Type: DEFAULT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.permissions ALTER COLUMN id SET DEFAULT nextval('public.permissions_id_seq'::regclass);


--
-- Name: politicas_gastos id; Type: DEFAULT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.politicas_gastos ALTER COLUMN id SET DEFAULT nextval('public.politicas_gastos_id_seq'::regclass);


--
-- Name: politicas_gastos_auditoria id; Type: DEFAULT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.politicas_gastos_auditoria ALTER COLUMN id SET DEFAULT nextval('public.politicas_gastos_auditoria_id_seq'::regclass);


--
-- Name: politicas_gastos_versiones id; Type: DEFAULT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.politicas_gastos_versiones ALTER COLUMN id SET DEFAULT nextval('public.politicas_gastos_versiones_id_seq'::regclass);


--
-- Name: proyectos id; Type: DEFAULT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.proyectos ALTER COLUMN id SET DEFAULT nextval('public.proyectos_id_seq'::regclass);


--
-- Name: roles id; Type: DEFAULT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.roles ALTER COLUMN id SET DEFAULT nextval('public.roles_id_seq'::regclass);


--
-- Name: solicitud_aprobaciones id; Type: DEFAULT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.solicitud_aprobaciones ALTER COLUMN id SET DEFAULT nextval('public.solicitud_aprobaciones_id_seq'::regclass);


--
-- Name: solicitud_detalles id; Type: DEFAULT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.solicitud_detalles ALTER COLUMN id SET DEFAULT nextval('public.solicitud_detalles_id_seq'::regclass);


--
-- Name: solicitudes id; Type: DEFAULT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.solicitudes ALTER COLUMN id SET DEFAULT nextval('public.solicitudes_id_seq'::regclass);


--
-- Name: solicitudes_auditoria id; Type: DEFAULT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.solicitudes_auditoria ALTER COLUMN id SET DEFAULT nextval('public.solicitudes_auditoria_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Data for Name: areas; Type: TABLE DATA; Schema: public; Owner: laravel_user
--

COPY public.areas (id, codigo, nombre, estatus, created_at, updated_at) FROM stdin;
1	RRHH	Recursos Humanos	t	2026-05-05 23:50:23	2026-05-05 23:50:23
2	IT	Tecnología de la Información	t	2026-05-05 23:57:40	2026-05-05 23:57:40
3	FN	Finanzas	t	2026-05-05 23:57:48	2026-05-05 23:57:48
4	VNT	Ventas	t	2026-05-05 23:58:03	2026-05-05 23:58:03
5	MKT	Marketing	t	2026-05-05 23:58:11	2026-05-05 23:58:11
\.


--
-- Data for Name: cache; Type: TABLE DATA; Schema: public; Owner: laravel_user
--

COPY public.cache (key, value, expiration) FROM stdin;
aurumflow-cache-spatie.permission.cache	a:3:{s:5:"alias";a:4:{s:1:"a";s:2:"id";s:1:"b";s:4:"name";s:1:"c";s:10:"guard_name";s:1:"r";s:5:"roles";}s:11:"permissions";a:53:{i:0;a:4:{s:1:"a";i:1;s:1:"b";s:23:"solicitudes.ver.propias";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:1;i:1;i:2;i:2;i:4;i:3;i:3;}}i:1;a:4:{s:1:"a";i:2;s:1:"b";s:20:"solicitudes.ver.area";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:2;a:4:{s:1:"a";i:3;s:1:"b";s:21:"solicitudes.ver.todas";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:4;}}i:3;a:4:{s:1:"a";i:4;s:1:"b";s:17:"solicitudes.crear";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:1;i:1;i:2;i:2;i:4;i:3;i:3;}}i:4;a:4:{s:1:"a";i:5;s:1:"b";s:18:"solicitudes.editar";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:1;i:1;i:2;i:2;i:4;i:3;i:3;}}i:5;a:4:{s:1:"a";i:6;s:1:"b";s:20:"solicitudes.eliminar";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:1;i:1;i:2;i:2;i:4;i:3;i:3;}}i:6;a:4:{s:1:"a";i:7;s:1:"b";s:18:"solicitudes.enviar";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:1;i:1;i:2;i:2;i:4;i:3;i:3;}}i:7;a:4:{s:1:"a";i:8;s:1:"b";s:19:"solicitudes.aprobar";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:8;a:4:{s:1:"a";i:9;s:1:"b";s:20:"solicitudes.rechazar";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:9;a:4:{s:1:"a";i:10;s:1:"b";s:18:"gastos.ver.propios";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:1;i:1;i:2;i:2;i:4;i:3;i:3;}}i:10;a:4:{s:1:"a";i:11;s:1:"b";s:15:"gastos.ver.area";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:11;a:4:{s:1:"a";i:12;s:1:"b";s:16:"gastos.ver.todos";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:4;}}i:12;a:4:{s:1:"a";i:13;s:1:"b";s:12:"gastos.crear";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:1;i:1;i:2;i:2;i:4;i:3;i:3;}}i:13;a:4:{s:1:"a";i:14;s:1:"b";s:13:"gastos.editar";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:1;i:1;i:2;i:2;i:4;i:3;i:3;}}i:14;a:4:{s:1:"a";i:15;s:1:"b";s:15:"gastos.eliminar";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:15;a:4:{s:1:"a";i:16;s:1:"b";s:24:"gastos.subir.comprobante";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:1;i:1;i:2;i:2;i:4;i:3;i:3;}}i:16;a:4:{s:1:"a";i:17;s:1:"b";s:14:"gastos.validar";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:4;}}i:17;a:4:{s:1:"a";i:18;s:1:"b";s:20:"comprobantes.validar";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:18;a:4:{s:1:"a";i:19;s:1:"b";s:15:"excepciones.ver";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:19;a:4:{s:1:"a";i:20;s:1:"b";s:26:"excepciones.aprobar.nivel1";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:20;a:4:{s:1:"a";i:21;s:1:"b";s:26:"excepciones.aprobar.nivel2";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:4;}}i:21;a:4:{s:1:"a";i:22;s:1:"b";s:27:"excepciones.rechazar.nivel1";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:22;a:4:{s:1:"a";i:23;s:1:"b";s:27:"excepciones.rechazar.nivel2";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:4;}}i:23;a:4:{s:1:"a";i:24;s:1:"b";s:13:"auditoria.ver";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:24;a:4:{s:1:"a";i:25;s:1:"b";s:17:"auditoria.revisar";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:25;a:4:{s:1:"a";i:26;s:1:"b";s:21:"empleados.ver.propios";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:26;a:4:{s:1:"a";i:27;s:1:"b";s:18:"empleados.ver.area";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:27;a:4:{s:1:"a";i:28;s:1:"b";s:19:"empleados.ver.todos";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:28;a:4:{s:1:"a";i:29;s:1:"b";s:15:"empleados.crear";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:29;a:4:{s:1:"a";i:30;s:1:"b";s:16:"empleados.editar";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:30;a:4:{s:1:"a";i:31;s:1:"b";s:18:"empleados.eliminar";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:31;a:4:{s:1:"a";i:32;s:1:"b";s:9:"areas.ver";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:32;a:4:{s:1:"a";i:33;s:1:"b";s:11:"areas.crear";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:33;a:4:{s:1:"a";i:34;s:1:"b";s:12:"areas.editar";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:34;a:4:{s:1:"a";i:35;s:1:"b";s:14:"areas.eliminar";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:35;a:4:{s:1:"a";i:36;s:1:"b";s:18:"centros_costos.ver";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:36;a:4:{s:1:"a";i:37;s:1:"b";s:20:"centros_costos.crear";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:37;a:4:{s:1:"a";i:38;s:1:"b";s:21:"centros_costos.editar";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:38;a:4:{s:1:"a";i:39;s:1:"b";s:23:"centros_costos.eliminar";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:39;a:4:{s:1:"a";i:40;s:1:"b";s:13:"proyectos.ver";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:40;a:4:{s:1:"a";i:41;s:1:"b";s:15:"proyectos.crear";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:41;a:4:{s:1:"a";i:42;s:1:"b";s:16:"proyectos.editar";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:42;a:4:{s:1:"a";i:43;s:1:"b";s:18:"proyectos.eliminar";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:43;a:4:{s:1:"a";i:44;s:1:"b";s:13:"conceptos.ver";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:44;a:4:{s:1:"a";i:45;s:1:"b";s:15:"conceptos.crear";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:45;a:4:{s:1:"a";i:46;s:1:"b";s:16:"conceptos.editar";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:46;a:4:{s:1:"a";i:47;s:1:"b";s:18:"conceptos.eliminar";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:47;a:4:{s:1:"a";i:48;s:1:"b";s:13:"politicas.ver";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:48;a:4:{s:1:"a";i:49;s:1:"b";s:15:"politicas.crear";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:49;a:4:{s:1:"a";i:50;s:1:"b";s:16:"politicas.editar";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:50;a:4:{s:1:"a";i:51;s:1:"b";s:18:"politicas.eliminar";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:51;a:4:{s:1:"a";i:52;s:1:"b";s:12:"reportes.ver";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:52;a:4:{s:1:"a";i:53;s:1:"b";s:17:"reportes.exportar";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:4;}}}s:5:"roles";a:4:{i:0;a:3:{s:1:"a";i:1;s:1:"b";s:5:"admin";s:1:"c";s:3:"web";}i:1;a:3:{s:1:"a";i:2;s:1:"b";s:7:"manager";s:1:"c";s:3:"web";}i:2;a:3:{s:1:"a";i:4;s:1:"b";s:8:"finanzas";s:1:"c";s:3:"web";}i:3;a:3:{s:1:"a";i:3;s:1:"b";s:9:"operativo";s:1:"c";s:3:"web";}}}	1778111456
aurumflow-cache-conceptos.list.todos	a:2:{i:0;a:10:{s:2:"id";i:1;s:6:"codigo";s:14:"CONC-2026-0001";s:6:"nombre";s:9:"Alimentos";s:9:"categoria";s:13:"Alimentación";s:15:"tipo_aplicacion";s:6:"Diario";s:10:"aplica_iva";b:1;s:15:"tope_referencia";s:7:"1500.00";s:14:"vigencia_desde";N;s:14:"vigencia_hasta";N;s:5:"roles";a:1:{i:0;a:6:{s:2:"id";i:3;s:4:"name";s:9:"operativo";s:10:"guard_name";s:3:"web";s:10:"created_at";s:27:"2026-05-05T23:50:23.000000Z";s:10:"updated_at";s:27:"2026-05-05T23:50:23.000000Z";s:5:"pivot";a:2:{s:11:"concepto_id";i:1;s:6:"rol_id";i:3;}}}}i:1;a:10:{s:2:"id";i:2;s:6:"codigo";s:14:"CONC-2026-0002";s:6:"nombre";s:11:"Combustible";s:9:"categoria";s:10:"Transporte";s:15:"tipo_aplicacion";s:5:"Viaje";s:10:"aplica_iva";b:1;s:15:"tope_referencia";s:7:"2500.00";s:14:"vigencia_desde";N;s:14:"vigencia_hasta";N;s:5:"roles";a:1:{i:0;a:6:{s:2:"id";i:3;s:4:"name";s:9:"operativo";s:10:"guard_name";s:3:"web";s:10:"created_at";s:27:"2026-05-05T23:50:23.000000Z";s:10:"updated_at";s:27:"2026-05-05T23:50:23.000000Z";s:5:"pivot";a:2:{s:11:"concepto_id";i:2;s:6:"rol_id";i:3;}}}}}	1778026426
aurumflow-cache-centros_costos.list.activos	a:4:{i:0;a:4:{s:2:"id";i:1;s:6:"nombre";s:15:"Oficina Central";s:6:"codigo";s:13:"CECO-2026-001";s:15:"cuenta_contable";s:10:"102-01-001";}i:1;a:4:{s:2:"id";i:3;s:6:"nombre";s:20:"Oficina Centro-Norte";s:6:"codigo";s:14:"CECO-2026-0002";s:15:"cuenta_contable";s:10:"104-02-001";}i:2;a:4:{s:2:"id";i:4;s:6:"nombre";s:15:"Oficina Orizaba";s:6:"codigo";s:14:"CECO-2026-0003";s:15:"cuenta_contable";s:10:"303-03-001";}i:3;a:4:{s:2:"id";i:2;s:6:"nombre";s:17:"Operaciones Norte";s:6:"codigo";s:14:"CECO-2026-0001";s:15:"cuenta_contable";s:10:"201-01-001";}}	1778028708
aurumflow-cache-proyectos.regiones	a:2:{i:0;s:12:"Centro-Norte";i:1;s:7:"Noreste";}	1778028708
aurumflow-cache-4fb02c1ee9d1080c2be7edd21d94e275:timer	i:1778028186;	1778028186
aurumflow-cache-4fb02c1ee9d1080c2be7edd21d94e275	i:1;	1778028187
aurumflow-cache-f4a762ff5eced503599af5084684f92a:timer	i:1778028260;	1778028260
aurumflow-cache-f4a762ff5eced503599af5084684f92a	i:1;	1778028260
aurumflow-cache-77de68daecd823babbb58edb1c8e14d7106e83bb	i:3;	1778033701
aurumflow-cache-areas.list.activas	a:5:{i:0;a:3:{s:2:"id";i:3;s:6:"nombre";s:8:"Finanzas";s:6:"codigo";s:2:"FN";}i:1;a:3:{s:2:"id";i:5;s:6:"nombre";s:9:"Marketing";s:6:"codigo";s:3:"MKT";}i:2;a:3:{s:2:"id";i:1;s:6:"nombre";s:16:"Recursos Humanos";s:6:"codigo";s:4:"RRHH";}i:3;a:3:{s:2:"id";i:2;s:6:"nombre";s:30:"Tecnología de la Información";s:6:"codigo";s:2:"IT";}i:4;a:3:{s:2:"id";i:4;s:6:"nombre";s:6:"Ventas";s:6:"codigo";s:3:"VNT";}}	1778034711
aurumflow-cache-929abd871d78b5bf916b7648c10440a7:timer	i:1778034335;	1778034335
aurumflow-cache-929abd871d78b5bf916b7648c10440a7	i:1;	1778034335
aurumflow-cache-77de68daecd823babbb58edb1c8e14d7106e83bb:timer	i:1778033701;	1778033701
aurumflow-cache-conceptos.list.3	a:2:{i:0;a:10:{s:2:"id";i:1;s:6:"codigo";s:14:"CONC-2026-0001";s:6:"nombre";s:9:"Alimentos";s:9:"categoria";s:13:"Alimentación";s:15:"tipo_aplicacion";s:6:"Diario";s:10:"aplica_iva";b:1;s:15:"tope_referencia";s:7:"1500.00";s:14:"vigencia_desde";N;s:14:"vigencia_hasta";N;s:5:"roles";a:1:{i:0;a:6:{s:2:"id";i:3;s:4:"name";s:9:"operativo";s:10:"guard_name";s:3:"web";s:10:"created_at";s:27:"2026-05-05T23:50:23.000000Z";s:10:"updated_at";s:27:"2026-05-05T23:50:23.000000Z";s:5:"pivot";a:2:{s:11:"concepto_id";i:1;s:6:"rol_id";i:3;}}}}i:1;a:10:{s:2:"id";i:2;s:6:"codigo";s:14:"CONC-2026-0002";s:6:"nombre";s:11:"Combustible";s:9:"categoria";s:10:"Transporte";s:15:"tipo_aplicacion";s:5:"Viaje";s:10:"aplica_iva";b:1;s:15:"tope_referencia";s:7:"2500.00";s:14:"vigencia_desde";N;s:14:"vigencia_hasta";N;s:5:"roles";a:1:{i:0;a:6:{s:2:"id";i:3;s:4:"name";s:9:"operativo";s:10:"guard_name";s:3:"web";s:10:"created_at";s:27:"2026-05-05T23:50:23.000000Z";s:10:"updated_at";s:27:"2026-05-05T23:50:23.000000Z";s:5:"pivot";a:2:{s:11:"concepto_id";i:2;s:6:"rol_id";i:3;}}}}}	1778034940
aurumflow-cache-proyectos.list.activos	a:1:{i:0;a:3:{s:2:"id";i:1;s:6:"nombre";s:29:"Supervisión Planta Monterrey";s:6:"codigo";s:13:"PRY-2026-0001";}}	1778036922
\.


--
-- Data for Name: cache_locks; Type: TABLE DATA; Schema: public; Owner: laravel_user
--

COPY public.cache_locks (key, owner, expiration) FROM stdin;
\.


--
-- Data for Name: centros_costos; Type: TABLE DATA; Schema: public; Owner: laravel_user
--

COPY public.centros_costos (id, codigo, nombre, cuenta_contable, estatus, created_at, updated_at) FROM stdin;
1	CECO-2026-001	Oficina Central	102-01-001	t	2026-05-05 23:50:23	2026-05-05 23:50:23
2	CECO-2026-0001	Operaciones Norte	201-01-001	t	2026-05-05 23:59:57	2026-05-05 23:59:57
3	CECO-2026-0002	Oficina Centro-Norte	104-02-001	t	2026-05-06 00:00:17	2026-05-06 00:00:17
4	CECO-2026-0003	Oficina Orizaba	303-03-001	t	2026-05-06 00:00:29	2026-05-06 00:00:29
\.


--
-- Data for Name: concepto_rol; Type: TABLE DATA; Schema: public; Owner: laravel_user
--

COPY public.concepto_rol (concepto_id, rol_id) FROM stdin;
1	3
2	3
\.


--
-- Data for Name: conceptos; Type: TABLE DATA; Schema: public; Owner: laravel_user
--

COPY public.conceptos (id, codigo, nombre, categoria, descripcion, tipo_aplicacion, orden, aplica_iva, tope_referencia, vigencia_desde, vigencia_hasta, estatus, created_at, updated_at) FROM stdin;
1	CONC-2026-0001	Alimentos	Alimentación	\N	Diario	0	t	1500.00	\N	\N	t	2026-05-06 00:03:07	2026-05-06 00:03:07
2	CONC-2026-0002	Combustible	Transporte	\N	Viaje	0	t	2500.00	\N	\N	t	2026-05-06 00:03:34	2026-05-06 00:03:34
\.


--
-- Data for Name: empleados; Type: TABLE DATA; Schema: public; Owner: laravel_user
--

COPY public.empleados (id, user_id, nombre_completo, must_change_password, puesto, area_id, centro_costo_id, rfc, curp, numero_nomina, banco_nomina, cuenta_nomina, clabe_nomina, nss, fecha_ingreso, telefono, tarjeta_credito_corporativa_asignada, limite_credito_tarjeta, estatus, created_at, updated_at, deleted_at) FROM stdin;
1	1	Admin Aurum	t	Administrador del Sistema	1	\N	XAXX010101000	XAXX010101HXXXXX00	NOM-0001	BBVA	1234567890	012180001234567890	12345678901	2025-05-05	\N	f	\N	t	2026-05-05 23:50:24	2026-05-05 23:50:24	\N
4	4	María González Ruiz	t	Analista de Finanzas	3	1	GOMR900201AB2	GOMR900201MXXXXX00	NOM-0150	BBVA	1234567890	012180001234567890	12345678901	2025-05-05		f	\N	t	2026-05-05 23:50:24	2026-05-06 00:19:39	\N
2	2	David De Santiago García	t	Gerente de Ventas	2	2	SADD01000KSJA	SADD010101HXXXXX00	NOM-0050	BBVA	1234567890	012180001234567890	12345678901	2025-05-05		t	100000.00	t	2026-05-05 23:50:24	2026-05-06 00:20:41	\N
3	3	Juan Pérez López	t	Analista de Operaciones	2	2	PELJ900101AB1	PELJ900101HXXXXX00	NOM-0100	BBVA	1234567890	012180001234567890	12345678901	2025-05-05		f	\N	t	2026-05-05 23:50:24	2026-05-06 00:21:01	\N
\.


--
-- Data for Name: failed_jobs; Type: TABLE DATA; Schema: public; Owner: laravel_user
--

COPY public.failed_jobs (id, uuid, connection, queue, payload, exception, failed_at) FROM stdin;
1	69b40ed3-79ca-40cb-b09d-ea032b086795	database	sat_high	{"uuid":"69b40ed3-79ca-40cb-b09d-ea032b086795","displayName":"App\\\\Jobs\\\\ValidarCFDIJob","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":5,"maxExceptions":null,"failOnTimeout":false,"backoff":"60,300,900,1800,3600","timeout":null,"retryUntil":null,"deleteWhenMissingModels":false,"data":{"commandName":"App\\\\Jobs\\\\ValidarCFDIJob","command":"O:23:\\"App\\\\Jobs\\\\ValidarCFDIJob\\":3:{s:13:\\"comprobanteId\\";i:1;s:8:\\"cfdiData\\";a:10:{s:4:\\"uuid\\";s:36:\\"4F8D9FD6-8703-5C10-801D-E0497C1E0E44\\";s:7:\\"version\\";s:3:\\"4.0\\";s:12:\\"version_cfdi\\";s:3:\\"4.0\\";s:6:\\"emisor\\";s:13:\\"AAHE840404N10\\";s:10:\\"rfc_emisor\\";s:13:\\"AAHE840404N10\\";s:8:\\"receptor\\";s:12:\\"FIN870710Q40\\";s:12:\\"rfc_receptor\\";s:12:\\"FIN870710Q40\\";s:5:\\"total\\";d:269.95;s:5:\\"fecha\\";s:19:\\"2025-05-05T09:44:59\\";s:10:\\"estado_sat\\";s:9:\\"pendiente\\";}s:5:\\"queue\\";s:8:\\"sat_high\\";}","batchId":null},"createdAt":1778028942,"delay":null}	Error: Call to undefined method PhpCfdi\\SatEstadoCfdi\\CfdiStatus::documento() in C:\\Users\\carlo\\Documents\\developments\\aurum-flow\\app\\Jobs\\ValidarCFDIJob.php:47\nStack trace:\n#0 C:\\Users\\carlo\\Documents\\developments\\aurum-flow\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): App\\Jobs\\ValidarCFDIJob->handle()\n#1 C:\\Users\\carlo\\Documents\\developments\\aurum-flow\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#2 C:\\Users\\carlo\\Documents\\developments\\aurum-flow\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure()\n#3 C:\\Users\\carlo\\Documents\\developments\\aurum-flow\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod()\n#4 C:\\Users\\carlo\\Documents\\developments\\aurum-flow\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call()\n#5 C:\\Users\\carlo\\Documents\\developments\\aurum-flow\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(136): Illuminate\\Container\\Container->call()\n#6 C:\\Users\\carlo\\Documents\\developments\\aurum-flow\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}()\n#7 C:\\Users\\carlo\\Documents\\developments\\aurum-flow\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}()\n#8 C:\\Users\\carlo\\Documents\\developments\\aurum-flow\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(140): Illuminate\\Pipeline\\Pipeline->then()\n#9 C:\\Users\\carlo\\Documents\\developments\\aurum-flow\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(134): Illuminate\\Bus\\Dispatcher->dispatchNow()\n#10 C:\\Users\\carlo\\Documents\\developments\\aurum-flow\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(180): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}()\n#11 C:\\Users\\carlo\\Documents\\developments\\aurum-flow\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(137): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}()\n#12 C:\\Users\\carlo\\Documents\\developments\\aurum-flow\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(127): Illuminate\\Pipeline\\Pipeline->then()\n#13 C:\\Users\\carlo\\Documents\\developments\\aurum-flow\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(68): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware()\n#14 C:\\Users\\carlo\\Documents\\developments\\aurum-flow\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call()\n#15 C:\\Users\\carlo\\Documents\\developments\\aurum-flow\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(502): Illuminate\\Queue\\Jobs\\Job->fire()\n#16 C:\\Users\\carlo\\Documents\\developments\\aurum-flow\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(450): Illuminate\\Queue\\Worker->process()\n#17 C:\\Users\\carlo\\Documents\\developments\\aurum-flow\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(215): Illuminate\\Queue\\Worker->runJob()\n#18 C:\\Users\\carlo\\Documents\\developments\\aurum-flow\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon()\n#19 C:\\Users\\carlo\\Documents\\developments\\aurum-flow\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker()\n#20 C:\\Users\\carlo\\Documents\\developments\\aurum-flow\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#21 C:\\Users\\carlo\\Documents\\developments\\aurum-flow\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#22 C:\\Users\\carlo\\Documents\\developments\\aurum-flow\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure()\n#23 C:\\Users\\carlo\\Documents\\developments\\aurum-flow\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod()\n#24 C:\\Users\\carlo\\Documents\\developments\\aurum-flow\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call()\n#25 C:\\Users\\carlo\\Documents\\developments\\aurum-flow\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(280): Illuminate\\Container\\Container->call()\n#26 C:\\Users\\carlo\\Documents\\developments\\aurum-flow\\vendor\\symfony\\console\\Command\\Command.php(341): Illuminate\\Console\\Command->execute()\n#27 C:\\Users\\carlo\\Documents\\developments\\aurum-flow\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(249): Symfony\\Component\\Console\\Command\\Command->run()\n#28 C:\\Users\\carlo\\Documents\\developments\\aurum-flow\\vendor\\symfony\\console\\Application.php(1117): Illuminate\\Console\\Command->run()\n#29 C:\\Users\\carlo\\Documents\\developments\\aurum-flow\\vendor\\symfony\\console\\Application.php(356): Symfony\\Component\\Console\\Application->doRunCommand()\n#30 C:\\Users\\carlo\\Documents\\developments\\aurum-flow\\vendor\\symfony\\console\\Application.php(195): Symfony\\Component\\Console\\Application->doRun()\n#31 C:\\Users\\carlo\\Documents\\developments\\aurum-flow\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(198): Symfony\\Component\\Console\\Application->run()\n#32 C:\\Users\\carlo\\Documents\\developments\\aurum-flow\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Application.php(1235): Illuminate\\Foundation\\Console\\Kernel->handle()\n#33 C:\\Users\\carlo\\Documents\\developments\\aurum-flow\\artisan(16): Illuminate\\Foundation\\Application->handleCommand()\n#34 {main}	2026-05-06 00:59:49
\.


--
-- Data for Name: flujos_aprobacion; Type: TABLE DATA; Schema: public; Owner: laravel_user
--

COPY public.flujos_aprobacion (id, tipo_solicitud, role_id, orden, requerido, minimo_aprobaciones, estatus, created_at, updated_at) FROM stdin;
1	viaticos	1	1	f	2	t	2026-05-05 23:50:24	2026-05-05 23:50:24
2	viaticos	2	2	f	2	t	2026-05-05 23:50:24	2026-05-05 23:50:24
3	viaticos	4	3	f	2	t	2026-05-05 23:50:24	2026-05-05 23:50:24
\.


--
-- Data for Name: folio_counters; Type: TABLE DATA; Schema: public; Owner: laravel_user
--

COPY public.folio_counters (id, prefix, year, current) FROM stdin;
1	CECO	2026	3
4	CONC	2026	2
6	PRY	2026	1
7	RT	2026	1
8	SOL	2026	2
\.


--
-- Data for Name: gasto_comprobantes; Type: TABLE DATA; Schema: public; Owner: laravel_user
--

COPY public.gasto_comprobantes (id, gasto_id, archivo, tipo, uuid, validacion_manual, validado_por, monto, subido_por, fecha_subida, created_at, updated_at, sat_status, sat_checked_at, sat_attempts, meta_cfdi, sat_last_error, fecha_gasto, comentario_validacion, validado_en, archivo_pdf) FROM stdin;
5	2	comprobantes/20260506/juan-perez-lopez_SOL-2026-0002_combustible_20260506_021253.xml	factura	4F8D9FD6-8703-5C10-801D-E0497C1E0E44	aprobado	\N	269.95	3	2026-05-06 02:12:54	2026-05-06 02:12:53	2026-05-06 02:12:53	vigente	\N	0	{"uuid":"4F8D9FD6-8703-5C10-801D-E0497C1E0E44","version":"4.0","version_cfdi":"4.0","emisor":"AAHE840404N10","rfc_emisor":"AAHE840404N10","receptor":"FIN870710Q40","rfc_receptor":"FIN870710Q40","total":269.95,"fecha":"2025-05-05T09:44:59","estado_sat":"vigente"}	\N	2026-04-01	\N	\N	comprobantes/20260506/juan-perez-lopez_SOL-2026-0002_combustible_20260506_021253_factura.pdf
9	1	comprobantes/20260506/juan-perez-lopez_SOL-2026-0002_alimentos_20260506_021455.jpg	pdf	\N	aprobado	4	500.00	3	2026-05-06 02:14:55	2026-05-06 02:14:55	2026-05-06 02:27:16	\N	\N	0	\N	\N	2026-04-02	\N	2026-05-06 02:27:16	\N
8	1	comprobantes/20260506/juan-perez-lopez_SOL-2026-0002_alimentos_20260506_021455.jpg	pdf	\N	aprobado	4	300.00	3	2026-05-06 02:14:55	2026-05-06 02:14:55	2026-05-06 02:27:21	\N	\N	0	\N	\N	2026-04-02	\N	2026-05-06 02:27:21	\N
10	1	comprobantes/20260506/juan-perez-lopez_SOL-2026-0002_alimentos_20260506_021455.png	pdf	\N	aprobado	4	200.00	3	2026-05-06 02:14:55	2026-05-06 02:14:55	2026-05-06 02:27:25	\N	\N	0	\N	\N	2026-04-02	\N	2026-05-06 02:27:25	\N
11	1	comprobantes/20260506/juan-perez-lopez_SOL-2026-0002_alimentos_20260506_021455.jpg	pdf	\N	aprobado	4	500.00	3	2026-05-06 02:14:55	2026-05-06 02:14:55	2026-05-06 02:27:30	\N	\N	0	\N	\N	2026-04-02	\N	2026-05-06 02:27:30	\N
12	1	comprobantes/20260506/juan-perez-lopez_SOL-2026-0002_alimentos_20260506_021455.jpg	pdf	\N	aprobado	4	30.00	3	2026-05-06 02:14:55	2026-05-06 02:14:55	2026-05-06 02:27:35	\N	\N	0	\N	\N	2026-04-02	\N	2026-05-06 02:27:35	\N
6	1	comprobantes/20260506/juan-perez-lopez_SOL-2026-0002_alimentos_20260506_021454.png	pdf	\N	aprobado	4	500.00	3	2026-05-06 02:14:55	2026-05-06 02:14:54	2026-05-06 02:27:40	\N	\N	0	\N	\N	2026-04-02	\N	2026-05-06 02:27:40	\N
7	1	comprobantes/20260506/juan-perez-lopez_SOL-2026-0002_alimentos_20260506_021454.jpg	pdf	\N	aprobado	4	400.00	3	2026-05-06 02:14:55	2026-05-06 02:14:54	2026-05-06 02:27:44	\N	\N	0	\N	\N	2026-04-02	\N	2026-05-06 02:27:44	\N
\.


--
-- Data for Name: gastos; Type: TABLE DATA; Schema: public; Owner: laravel_user
--

COPY public.gastos (id, solicitud_id, concepto_id, fecha_gasto, monto, rfc_proveedor, uuid_factura, archivo_xml, archivo_pdf, estatus, created_at, updated_at, deleted_at) FROM stdin;
2	2	2	2026-05-06	269.95	\N	\N	\N	\N	comprobado	2026-05-06 00:49:10	2026-05-06 02:12:54	\N
1	2	1	2026-05-06	2430.00	\N	\N	\N	\N	comprobado	2026-05-06 00:49:10	2026-05-06 02:27:44	\N
\.


--
-- Data for Name: gastos_auditoria; Type: TABLE DATA; Schema: public; Owner: laravel_user
--

COPY public.gastos_auditoria (id, gasto_id, excepcion_id, evento, actor_id, origen, datos_antes, datos_despues, created_at) FROM stdin;
1	\N	\N	aprobado	2	sistema	\N	"{\\"role\\":\\"manager\\",\\"comentario\\":null}"	2026-05-06 00:42:59
2	\N	\N	rechazado	4	sistema	\N	"{\\"role\\":\\"finanzas\\",\\"comentario\\":\\"El concepto de combustible es muy alto en relaci\\\\u00f3n al tiempo que se estuvo\\"}"	2026-05-06 00:46:51
3	\N	\N	aprobado	4	sistema	\N	"{\\"role\\":\\"finanzas\\",\\"comentario\\":null}"	2026-05-06 00:48:52
4	\N	\N	aprobado	2	sistema	\N	"{\\"role\\":\\"manager\\",\\"comentario\\":null}"	2026-05-06 00:49:11
5	2	\N	monto_acumulado	3	sistema	"{\\"monto\\":\\"500.00\\"}"	"{\\"monto\\":269.95}"	2026-05-06 00:55:42
6	2	\N	validado	3	sistema	\N	"{\\"estatus\\":\\"aprobado\\"}"	2026-05-06 00:55:42
7	2	\N	comprobante_subido	3	sistema	\N	"{\\"tipo\\":\\"factura\\",\\"monto\\":\\"269.95\\",\\"uuid\\":\\"4F8D9FD6-8703-5C10-801D-E0497C1E0E44\\"}"	2026-05-06 00:55:42
8	2	\N	monto_acumulado	3	sistema	"{\\"monto\\":\\"269.95\\"}"	"{\\"monto\\":269.95}"	2026-05-06 01:43:12
9	2	\N	validado	3	sistema	\N	"{\\"estatus\\":\\"aprobado\\"}"	2026-05-06 01:43:12
10	2	\N	comprobante_subido	3	sistema	\N	"{\\"tipo\\":\\"factura\\",\\"monto\\":\\"269.95\\",\\"uuid\\":\\"4F8D9FD6-8703-5C10-801D-E0497C1E0E44\\"}"	2026-05-06 01:43:12
11	2	\N	monto_acumulado	3	sistema	"{\\"monto\\":\\"269.95\\"}"	"{\\"monto\\":269.95}"	2026-05-06 01:50:26
12	2	\N	validado	3	sistema	\N	"{\\"estatus\\":\\"aprobado\\"}"	2026-05-06 01:50:26
13	2	\N	comprobante_subido	3	sistema	\N	"{\\"tipo\\":\\"factura\\",\\"monto\\":\\"269.95\\",\\"uuid\\":\\"4F8D9FD6-8703-5C10-801D-E0497C1E0E44\\"}"	2026-05-06 01:50:26
14	2	\N	monto_acumulado	3	sistema	"{\\"monto\\":\\"269.95\\"}"	"{\\"monto\\":269.95}"	2026-05-06 02:09:48
15	2	\N	validado	3	sistema	\N	"{\\"estatus\\":\\"aprobado\\"}"	2026-05-06 02:09:48
16	2	\N	comprobante_subido	3	sistema	\N	"{\\"tipo\\":\\"factura\\",\\"monto\\":\\"269.95\\",\\"uuid\\":\\"4F8D9FD6-8703-5C10-801D-E0497C1E0E44\\"}"	2026-05-06 02:09:48
17	2	\N	monto_acumulado	3	sistema	"{\\"monto\\":\\"269.95\\"}"	"{\\"monto\\":269.95}"	2026-05-06 02:12:54
18	2	\N	validado	3	sistema	\N	"{\\"estatus\\":\\"aprobado\\"}"	2026-05-06 02:12:54
19	2	\N	comprobante_subido	3	sistema	\N	"{\\"tipo\\":\\"factura\\",\\"monto\\":\\"269.95\\",\\"uuid\\":\\"4F8D9FD6-8703-5C10-801D-E0497C1E0E44\\"}"	2026-05-06 02:12:54
20	1	\N	monto_acumulado	3	sistema	"{\\"monto\\":\\"1500.00\\"}"	"{\\"monto\\":500}"	2026-05-06 02:14:55
21	1	\N	validado	3	sistema	\N	"{\\"estatus\\":\\"aprobado\\"}"	2026-05-06 02:14:55
22	1	\N	comprobante_subido	3	sistema	\N	"{\\"tipo\\":\\"pdf\\",\\"monto\\":\\"500.00\\",\\"uuid\\":null}"	2026-05-06 02:14:55
23	1	\N	monto_acumulado	3	sistema	"{\\"monto\\":\\"500.00\\"}"	"{\\"monto\\":900}"	2026-05-06 02:14:55
24	1	\N	validado	3	sistema	\N	"{\\"estatus\\":\\"aprobado\\"}"	2026-05-06 02:14:55
25	1	\N	comprobante_subido	3	sistema	\N	"{\\"tipo\\":\\"pdf\\",\\"monto\\":\\"400.00\\",\\"uuid\\":null}"	2026-05-06 02:14:55
26	1	\N	monto_acumulado	3	sistema	"{\\"monto\\":\\"900.00\\"}"	"{\\"monto\\":1200}"	2026-05-06 02:14:55
27	1	\N	validado	3	sistema	\N	"{\\"estatus\\":\\"aprobado\\"}"	2026-05-06 02:14:55
28	1	\N	comprobante_subido	3	sistema	\N	"{\\"tipo\\":\\"pdf\\",\\"monto\\":\\"300.00\\",\\"uuid\\":null}"	2026-05-06 02:14:55
29	1	\N	monto_acumulado	3	sistema	"{\\"monto\\":\\"1200.00\\"}"	"{\\"monto\\":1700}"	2026-05-06 02:14:55
30	1	\N	validado	3	sistema	\N	"{\\"estatus\\":\\"excepcion\\"}"	2026-05-06 02:14:55
31	1	\N	comprobante_subido	3	sistema	\N	"{\\"tipo\\":\\"pdf\\",\\"monto\\":\\"500.00\\",\\"uuid\\":null}"	2026-05-06 02:14:55
32	1	\N	monto_acumulado	3	sistema	"{\\"monto\\":\\"1700.00\\"}"	"{\\"monto\\":1900}"	2026-05-06 02:14:55
33	1	\N	validado	3	sistema	\N	"{\\"estatus\\":\\"excepcion\\"}"	2026-05-06 02:14:55
34	1	\N	comprobante_subido	3	sistema	\N	"{\\"tipo\\":\\"pdf\\",\\"monto\\":\\"200.00\\",\\"uuid\\":null}"	2026-05-06 02:14:55
35	1	\N	monto_acumulado	3	sistema	"{\\"monto\\":\\"1900.00\\"}"	"{\\"monto\\":2400}"	2026-05-06 02:14:55
36	1	\N	validado	3	sistema	\N	"{\\"estatus\\":\\"excepcion\\"}"	2026-05-06 02:14:55
37	1	\N	comprobante_subido	3	sistema	\N	"{\\"tipo\\":\\"pdf\\",\\"monto\\":\\"500.00\\",\\"uuid\\":null}"	2026-05-06 02:14:55
38	1	\N	monto_acumulado	3	sistema	"{\\"monto\\":\\"2400.00\\"}"	"{\\"monto\\":2430}"	2026-05-06 02:14:55
39	1	\N	validado	3	sistema	\N	"{\\"estatus\\":\\"excepcion\\"}"	2026-05-06 02:14:55
40	1	\N	comprobante_subido	3	sistema	\N	"{\\"tipo\\":\\"pdf\\",\\"monto\\":\\"30.00\\",\\"uuid\\":null}"	2026-05-06 02:14:55
41	1	\N	comprobante_aprobado	4	sistema	\N	"{\\"comprobante_id\\":9,\\"motivo\\":null}"	2026-05-06 02:27:16
42	1	\N	comprobante_aprobado	4	sistema	\N	"{\\"comprobante_id\\":8,\\"motivo\\":null}"	2026-05-06 02:27:21
43	1	\N	comprobante_aprobado	4	sistema	\N	"{\\"comprobante_id\\":10,\\"motivo\\":null}"	2026-05-06 02:27:26
44	1	\N	comprobante_aprobado	4	sistema	\N	"{\\"comprobante_id\\":11,\\"motivo\\":null}"	2026-05-06 02:27:31
45	1	\N	comprobante_aprobado	4	sistema	\N	"{\\"comprobante_id\\":12,\\"motivo\\":null}"	2026-05-06 02:27:35
46	1	\N	comprobante_aprobado	4	sistema	\N	"{\\"comprobante_id\\":6,\\"motivo\\":null}"	2026-05-06 02:27:40
47	1	\N	comprobante_aprobado	4	sistema	\N	"{\\"comprobante_id\\":7,\\"motivo\\":null}"	2026-05-06 02:27:45
48	1	\N	comprobado	4	sistema	\N	"{\\"total_comprobado\\":\\"2430.00\\"}"	2026-05-06 02:27:45
\.


--
-- Data for Name: gastos_excepciones; Type: TABLE DATA; Schema: public; Owner: laravel_user
--

COPY public.gastos_excepciones (id, gasto_id, nivel, estatus, comentario, aprobado_por, resuelto_en, created_at, updated_at) FROM stdin;
1	1	1	aprobado	\N	2	2026-05-06 02:25:15	2026-05-06 02:14:55	2026-05-06 02:25:15
2	1	2	aprobado	\N	1	2026-05-06 02:25:32	2026-05-06 02:25:15	2026-05-06 02:25:32
\.


--
-- Data for Name: job_batches; Type: TABLE DATA; Schema: public; Owner: laravel_user
--

COPY public.job_batches (id, name, total_jobs, pending_jobs, failed_jobs, failed_job_ids, options, cancelled_at, created_at, finished_at) FROM stdin;
\.


--
-- Data for Name: jobs; Type: TABLE DATA; Schema: public; Owner: laravel_user
--

COPY public.jobs (id, queue, payload, attempts, reserved_at, available_at, created_at) FROM stdin;
\.


--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: laravel_user
--

COPY public.migrations (id, migration, batch) FROM stdin;
1	0001_01_01_000000_create_users_table	1
2	0001_01_01_000001_create_cache_table	1
3	0001_01_01_000002_create_jobs_table	1
4	2025_08_14_170933_add_two_factor_columns_to_users_table	1
5	2026_04_15_233241_create_conceptos_table	1
6	2026_04_16_014225_create_permission_tables	1
7	2026_04_16_014511_create_areas_table	1
8	2026_04_16_014536_create_centros_costos_table	1
9	2026_04_16_014540_create_empleados_table	1
10	2026_04_16_014541_create_proyectos_table	1
11	2026_04_16_014542_create_solicitudes_table	1
12	2026_04_16_014543_create_solicitud_detalles_table	1
13	2026_04_16_014544_create_gastos_table	1
14	2026_04_16_014545_create_gasto_excepciones_table	1
15	2026_04_16_014546_create_politicas_gastos_table	1
16	2026_04_16_014547_create_politicas_gastos_versiones_table	1
17	2026_04_16_014548_create_politicas_gastos_auditoria_table	1
18	2026_04_17_005737_add_must_change_password_to_users_table	1
19	2026_04_17_021837_add_estatus_to_users_table	1
20	2026_04_18_042959_create_gastos_excepciones_table	1
21	2026_04_18_172033_create_gastos_auditoria_table	1
22	2026_04_18_195116_add_motivo_cancelacion_to_solicitudes_table	1
23	2026_04_18_201624_create_gasto_comprobantes_table	1
24	2026_04_18_204624_create_solicitudes_auditoria_table	1
25	2026_04_18_211728_add_sat_fields_to_gasto_comprobantes	1
26	2026_04_25_014400_add_blocked_to_users_table	1
27	2026_04_26_182433_create_concepto_rol_table	1
28	2026_05_02_035052_create_folio_counters_table	1
29	2026_05_03_050401_create_flujos_aprobacion_table	1
30	2026_05_03_050423_create_solicitud_aprobaciones_table	1
31	2026_05_03_213535_add_justificacion_to_solicitud_detalles_table	1
32	2026_05_04_015811_drop_gasto_excepciones_table	1
33	2026_05_05_011518_add_fecha_gasto_to_gasto_comprobantes_table	1
34	2026_05_05_031031_add_validation_fields_to_gasto_comprobantes_table	1
35	2026_05_05_192252_add_archivo_pdf_to_gasto_comprobantes	1
\.


--
-- Data for Name: model_has_permissions; Type: TABLE DATA; Schema: public; Owner: laravel_user
--

COPY public.model_has_permissions (permission_id, model_type, model_id) FROM stdin;
\.


--
-- Data for Name: model_has_roles; Type: TABLE DATA; Schema: public; Owner: laravel_user
--

COPY public.model_has_roles (role_id, model_type, model_id) FROM stdin;
1	App\\Models\\User	1
4	App\\Models\\User	4
2	App\\Models\\User	2
3	App\\Models\\User	3
\.


--
-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: laravel_user
--

COPY public.password_reset_tokens (email, token, created_at) FROM stdin;
\.


--
-- Data for Name: permissions; Type: TABLE DATA; Schema: public; Owner: laravel_user
--

COPY public.permissions (id, name, guard_name, created_at, updated_at) FROM stdin;
1	solicitudes.ver.propias	web	2026-05-05 23:50:23	2026-05-05 23:50:23
2	solicitudes.ver.area	web	2026-05-05 23:50:23	2026-05-05 23:50:23
3	solicitudes.ver.todas	web	2026-05-05 23:50:23	2026-05-05 23:50:23
4	solicitudes.crear	web	2026-05-05 23:50:23	2026-05-05 23:50:23
5	solicitudes.editar	web	2026-05-05 23:50:23	2026-05-05 23:50:23
6	solicitudes.eliminar	web	2026-05-05 23:50:23	2026-05-05 23:50:23
7	solicitudes.enviar	web	2026-05-05 23:50:23	2026-05-05 23:50:23
8	solicitudes.aprobar	web	2026-05-05 23:50:23	2026-05-05 23:50:23
9	solicitudes.rechazar	web	2026-05-05 23:50:23	2026-05-05 23:50:23
10	gastos.ver.propios	web	2026-05-05 23:50:23	2026-05-05 23:50:23
11	gastos.ver.area	web	2026-05-05 23:50:23	2026-05-05 23:50:23
12	gastos.ver.todos	web	2026-05-05 23:50:23	2026-05-05 23:50:23
13	gastos.crear	web	2026-05-05 23:50:23	2026-05-05 23:50:23
14	gastos.editar	web	2026-05-05 23:50:23	2026-05-05 23:50:23
15	gastos.eliminar	web	2026-05-05 23:50:23	2026-05-05 23:50:23
16	gastos.subir.comprobante	web	2026-05-05 23:50:23	2026-05-05 23:50:23
17	gastos.validar	web	2026-05-05 23:50:23	2026-05-05 23:50:23
18	comprobantes.validar	web	2026-05-05 23:50:23	2026-05-05 23:50:23
19	excepciones.ver	web	2026-05-05 23:50:23	2026-05-05 23:50:23
20	excepciones.aprobar.nivel1	web	2026-05-05 23:50:23	2026-05-05 23:50:23
21	excepciones.aprobar.nivel2	web	2026-05-05 23:50:23	2026-05-05 23:50:23
22	excepciones.rechazar.nivel1	web	2026-05-05 23:50:23	2026-05-05 23:50:23
23	excepciones.rechazar.nivel2	web	2026-05-05 23:50:23	2026-05-05 23:50:23
24	auditoria.ver	web	2026-05-05 23:50:23	2026-05-05 23:50:23
25	auditoria.revisar	web	2026-05-05 23:50:23	2026-05-05 23:50:23
26	empleados.ver.propios	web	2026-05-05 23:50:23	2026-05-05 23:50:23
27	empleados.ver.area	web	2026-05-05 23:50:23	2026-05-05 23:50:23
28	empleados.ver.todos	web	2026-05-05 23:50:23	2026-05-05 23:50:23
29	empleados.crear	web	2026-05-05 23:50:23	2026-05-05 23:50:23
30	empleados.editar	web	2026-05-05 23:50:23	2026-05-05 23:50:23
31	empleados.eliminar	web	2026-05-05 23:50:23	2026-05-05 23:50:23
32	areas.ver	web	2026-05-05 23:50:23	2026-05-05 23:50:23
33	areas.crear	web	2026-05-05 23:50:23	2026-05-05 23:50:23
34	areas.editar	web	2026-05-05 23:50:23	2026-05-05 23:50:23
35	areas.eliminar	web	2026-05-05 23:50:23	2026-05-05 23:50:23
36	centros_costos.ver	web	2026-05-05 23:50:23	2026-05-05 23:50:23
37	centros_costos.crear	web	2026-05-05 23:50:23	2026-05-05 23:50:23
38	centros_costos.editar	web	2026-05-05 23:50:23	2026-05-05 23:50:23
39	centros_costos.eliminar	web	2026-05-05 23:50:23	2026-05-05 23:50:23
40	proyectos.ver	web	2026-05-05 23:50:23	2026-05-05 23:50:23
41	proyectos.crear	web	2026-05-05 23:50:23	2026-05-05 23:50:23
42	proyectos.editar	web	2026-05-05 23:50:23	2026-05-05 23:50:23
43	proyectos.eliminar	web	2026-05-05 23:50:23	2026-05-05 23:50:23
44	conceptos.ver	web	2026-05-05 23:50:23	2026-05-05 23:50:23
45	conceptos.crear	web	2026-05-05 23:50:23	2026-05-05 23:50:23
46	conceptos.editar	web	2026-05-05 23:50:23	2026-05-05 23:50:23
47	conceptos.eliminar	web	2026-05-05 23:50:23	2026-05-05 23:50:23
48	politicas.ver	web	2026-05-05 23:50:23	2026-05-05 23:50:23
49	politicas.crear	web	2026-05-05 23:50:23	2026-05-05 23:50:23
50	politicas.editar	web	2026-05-05 23:50:23	2026-05-05 23:50:23
51	politicas.eliminar	web	2026-05-05 23:50:23	2026-05-05 23:50:23
52	reportes.ver	web	2026-05-05 23:50:23	2026-05-05 23:50:23
53	reportes.exportar	web	2026-05-05 23:50:23	2026-05-05 23:50:23
\.


--
-- Data for Name: politicas_gastos; Type: TABLE DATA; Schema: public; Owner: laravel_user
--

COPY public.politicas_gastos (id, role_id, concepto_id, monto_max, tipo_limite, monto_libre, monto_comprobante, monto_factura, valida_sat, acumulable_dia, permite_excepcion, vigencia_desde, vigencia_hasta, estatus, created_at, updated_at, deleted_at) FROM stdin;
1	3	2	2500.00	Viaje	\N	\N	0.01	t	f	f	2026-04-01	2026-05-08	t	2026-05-06 00:04:59	2026-05-06 00:04:59	\N
2	3	2	3000.00	Evento	\N	\N	0.01	t	f	f	2026-05-11	2026-05-31	t	2026-05-06 00:05:35	2026-05-06 00:05:35	\N
3	3	1	1500.00	Diario	\N	0.01	\N	f	f	t	\N	\N	t	2026-05-06 00:06:12	2026-05-06 00:06:12	\N
\.


--
-- Data for Name: politicas_gastos_auditoria; Type: TABLE DATA; Schema: public; Owner: laravel_user
--

COPY public.politicas_gastos_auditoria (id, politica_id, version_id, evento, actor_id, origen, datos_antes, datos_despues, created_at) FROM stdin;
1	1	1	created	1	manual	\N	{"id": 1, "role_id": 3, "monto_max": "2500.00", "created_at": "2026-05-06T00:04:59.000000Z", "updated_at": "2026-05-06T00:04:59.000000Z", "valida_sat": true, "concepto_id": 2, "monto_libre": null, "tipo_limite": "Viaje", "monto_factura": "0.01", "acumulable_dia": false, "vigencia_desde": "2026-04-01T00:00:00.000000Z", "vigencia_hasta": "2026-05-08T00:00:00.000000Z", "monto_comprobante": null, "permite_excepcion": false}	2026-05-06 00:05:00
2	2	2	created	1	manual	\N	{"id": 2, "role_id": 3, "monto_max": "3000.00", "created_at": "2026-05-06T00:05:35.000000Z", "updated_at": "2026-05-06T00:05:35.000000Z", "valida_sat": true, "concepto_id": 2, "monto_libre": null, "tipo_limite": "Evento", "monto_factura": "0.01", "acumulable_dia": false, "vigencia_desde": "2026-05-11T00:00:00.000000Z", "vigencia_hasta": "2026-05-31T00:00:00.000000Z", "monto_comprobante": null, "permite_excepcion": false}	2026-05-06 00:05:35
3	3	3	created	1	manual	\N	{"id": 3, "role_id": 3, "monto_max": "1500.00", "created_at": "2026-05-06T00:06:12.000000Z", "updated_at": "2026-05-06T00:06:12.000000Z", "valida_sat": false, "concepto_id": 1, "monto_libre": null, "tipo_limite": "Diario", "monto_factura": null, "acumulable_dia": false, "vigencia_desde": null, "vigencia_hasta": null, "monto_comprobante": "0.01", "permite_excepcion": true}	2026-05-06 00:06:13
\.


--
-- Data for Name: politicas_gastos_versiones; Type: TABLE DATA; Schema: public; Owner: laravel_user
--

COPY public.politicas_gastos_versiones (id, politica_id, role_id, concepto_id, monto_max, tipo_limite, monto_libre, monto_comprobante, monto_factura, valida_sat, acumulable_dia, permite_excepcion, vigencia_desde, vigencia_hasta, motivo, creado_por, aprobado_por, approved_at, estatus, created_at, updated_at) FROM stdin;
1	1	3	2	2500.00	Viaje	\N	\N	0.01	t	f	f	2026-04-01	2026-05-08	Creación inicial	1	\N	2026-05-06 00:04:59	Aprobada	2026-05-06 00:04:59	2026-05-06 00:04:59
2	2	3	2	3000.00	Evento	\N	\N	0.01	t	f	f	2026-05-11	2026-05-31	Creación inicial	1	\N	2026-05-06 00:05:35	Aprobada	2026-05-06 00:05:35	2026-05-06 00:05:35
3	3	3	1	1500.00	Diario	\N	0.01	\N	f	f	t	\N	\N	Creación inicial	1	\N	2026-05-06 00:06:12	Aprobada	2026-05-06 00:06:12	2026-05-06 00:06:12
\.


--
-- Data for Name: proyectos; Type: TABLE DATA; Schema: public; Owner: laravel_user
--

COPY public.proyectos (id, codigo, nombre, cliente, tipo, descripcion, region, prioridad, estado_operativo, centro_costo_id, responsable_id, presupuesto_total, fecha_inicio, fecha_fin, pais, estado, ciudad, estatus, created_at, updated_at) FROM stdin;
1	PRY-2026-0001	Supervisión Planta Monterrey		Proyecto		Noreste	Media	Activo	2	\N	\N	\N	\N	México	Nuevo León	Monterrey	t	2026-05-06 00:08:18	2026-05-06 00:08:18
2	RT-2026-0001	Dataware		Ruta		Centro-Norte	Media	Draft	3	\N	\N	\N	\N	México	Querétaro	Santiago de Querétaro	t	2026-05-06 00:10:19	2026-05-06 00:10:19
\.


--
-- Data for Name: role_has_permissions; Type: TABLE DATA; Schema: public; Owner: laravel_user
--

COPY public.role_has_permissions (permission_id, role_id) FROM stdin;
1	1
2	1
3	1
4	1
5	1
6	1
7	1
8	1
9	1
10	1
11	1
12	1
13	1
14	1
15	1
16	1
17	1
18	1
19	1
20	1
21	1
22	1
23	1
24	1
25	1
26	1
27	1
28	1
29	1
30	1
31	1
32	1
33	1
34	1
35	1
36	1
37	1
38	1
39	1
40	1
41	1
42	1
43	1
44	1
45	1
46	1
47	1
48	1
49	1
50	1
51	1
52	1
53	1
1	2
2	2
4	2
5	2
7	2
6	2
8	2
9	2
10	2
11	2
13	2
14	2
16	2
18	2
19	2
20	2
22	2
26	2
27	2
40	2
48	2
44	2
52	2
24	2
3	4
1	4
4	4
5	4
7	4
6	4
8	4
9	4
10	4
12	4
17	4
13	4
14	4
16	4
18	4
19	4
21	4
23	4
52	4
53	4
24	4
48	4
1	3
4	3
5	3
7	3
6	3
10	3
13	3
14	3
16	3
\.


--
-- Data for Name: roles; Type: TABLE DATA; Schema: public; Owner: laravel_user
--

COPY public.roles (id, name, guard_name, created_at, updated_at) FROM stdin;
1	admin	web	2026-05-05 23:50:23	2026-05-05 23:50:23
2	manager	web	2026-05-05 23:50:23	2026-05-05 23:50:23
3	operativo	web	2026-05-05 23:50:23	2026-05-05 23:50:23
4	finanzas	web	2026-05-05 23:50:23	2026-05-05 23:50:23
\.


--
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: laravel_user
--

COPY public.sessions (id, user_id, ip_address, user_agent, payload, last_activity) FROM stdin;
\.


--
-- Data for Name: solicitud_aprobaciones; Type: TABLE DATA; Schema: public; Owner: laravel_user
--

COPY public.solicitud_aprobaciones (id, solicitud_id, role_id, user_id, accion, comentario, created_at) FROM stdin;
3	2	4	4	aprobado	\N	2026-05-06 00:48:52
4	2	2	2	aprobado	\N	2026-05-06 00:49:11
\.


--
-- Data for Name: solicitud_detalles; Type: TABLE DATA; Schema: public; Owner: laravel_user
--

COPY public.solicitud_detalles (id, solicitud_id, concepto_id, monto_estimado, created_at, updated_at, justificacion_exceso) FROM stdin;
1	1	1	1500.00	2026-05-06 00:33:03	2026-05-06 00:33:03	\N
2	1	2	2000.00	2026-05-06 00:33:11	2026-05-06 00:33:11	\N
3	1	1	1500.00	2026-05-06 00:33:34	2026-05-06 00:33:34	\N
4	1	1	1000.00	2026-05-06 00:33:39	2026-05-06 00:33:39	\N
5	1	1	1500.00	2026-05-06 00:33:45	2026-05-06 00:33:45	\N
6	1	1	1500.00	2026-05-06 00:33:53	2026-05-06 00:33:53	\N
7	1	1	1000.00	2026-05-06 00:34:04	2026-05-06 00:34:04	\N
9	2	1	1500.00	2026-05-06 00:41:36	2026-05-06 00:41:36	\N
10	2	2	500.00	2026-05-06 00:47:54	2026-05-06 00:47:54	\N
\.


--
-- Data for Name: solicitudes; Type: TABLE DATA; Schema: public; Owner: laravel_user
--

COPY public.solicitudes (id, folio, empleado_id, area_id, proyecto_id, fecha_solicitud, fecha_inicio, fecha_fin, motivo, monto_total, motivo_rechazo, estatus, created_at, updated_at, deleted_at, motivo_cancelacion) FROM stdin;
1	SOL-2026-0001	3	2	1	2026-05-06 00:13:01	2026-04-06	2026-04-11	Se requirio mi asistencia en la supervisión de la nueva planta en monterrey	10000.00	\N	Pendiente	2026-05-06 00:13:00	2026-05-06 00:34:17	\N	\N
2	SOL-2026-0002	3	2	1	2026-05-06 00:41:19	2026-04-01	2026-04-03	Asistencia en la supervisión de la nueva planta Monterrey	2000.00	\N	Comprobado	2026-05-06 00:41:18	2026-05-06 02:27:44	\N	\N
\.


--
-- Data for Name: solicitudes_auditoria; Type: TABLE DATA; Schema: public; Owner: laravel_user
--

COPY public.solicitudes_auditoria (id, solicitud_id, evento, actor_id, datos, created_at) FROM stdin;
1	1	created	3	\N	2026-05-06 00:13:01
2	1	enviado	3	\N	2026-05-06 00:34:18
3	2	created	3	\N	2026-05-06 00:41:19
4	2	enviado	3	\N	2026-05-06 00:41:39
5	2	reabierto	3	\N	2026-05-06 00:47:03
6	2	enviado	3	\N	2026-05-06 00:47:58
7	2	comprobado_automatico	4	{"total_gastos":2,"comprobados":2,"rechazados":0}	2026-05-06 02:27:45
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: laravel_user
--

COPY public.users (id, name, email, email_verified_at, password, remember_token, created_at, updated_at, two_factor_secret, two_factor_recovery_codes, two_factor_confirmed_at, must_change_password, active, blocked) FROM stdin;
3	Juan Pérez López	operativo@demo.com	\N	$2y$12$X0lriHNUmH71uTXfrj/Aw.ZPvZqhXyRlUyuoYTcupWtOmp0Tx4KRS	\N	2026-05-05 23:50:24	2026-05-05 23:51:10	\N	\N	\N	f	t	f
1	Admin Aurum	admin@demo.com	\N	$2y$12$58TdJP2sLZkF7ZBIUj7XbuUfJGOQ3asW7fBexL0qZvzM3nj5DDfhy	\N	2026-05-05 23:50:23	2026-05-05 23:52:17	\N	\N	\N	f	t	f
2	David De Santiago García	gerente@demo.com	\N	$2y$12$TWEZLvCmN1TuUxsvi.ikLOACFboHW6jJvw9mrkJ9KjJCa67BqEeDO	\N	2026-05-05 23:50:23	2026-05-06 00:42:17	\N	\N	\N	f	t	f
4	María González Ruiz	finanzas@demo.com	\N	$2y$12$J9E.cfbiEL87xM4Yl6CV2OERmLGxLSDuMLwE.0ZbfFLflmxeH0aYC	\N	2026-05-05 23:50:24	2026-05-06 00:45:53	\N	\N	\N	f	t	f
\.


--
-- Name: areas_id_seq; Type: SEQUENCE SET; Schema: public; Owner: laravel_user
--

SELECT pg_catalog.setval('public.areas_id_seq', 5, true);


--
-- Name: centros_costos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: laravel_user
--

SELECT pg_catalog.setval('public.centros_costos_id_seq', 4, true);


--
-- Name: conceptos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: laravel_user
--

SELECT pg_catalog.setval('public.conceptos_id_seq', 2, true);


--
-- Name: empleados_id_seq; Type: SEQUENCE SET; Schema: public; Owner: laravel_user
--

SELECT pg_catalog.setval('public.empleados_id_seq', 4, true);


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: laravel_user
--

SELECT pg_catalog.setval('public.failed_jobs_id_seq', 2, true);


--
-- Name: flujos_aprobacion_id_seq; Type: SEQUENCE SET; Schema: public; Owner: laravel_user
--

SELECT pg_catalog.setval('public.flujos_aprobacion_id_seq', 3, true);


--
-- Name: folio_counters_id_seq; Type: SEQUENCE SET; Schema: public; Owner: laravel_user
--

SELECT pg_catalog.setval('public.folio_counters_id_seq', 9, true);


--
-- Name: gasto_comprobantes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: laravel_user
--

SELECT pg_catalog.setval('public.gasto_comprobantes_id_seq', 12, true);


--
-- Name: gastos_auditoria_id_seq; Type: SEQUENCE SET; Schema: public; Owner: laravel_user
--

SELECT pg_catalog.setval('public.gastos_auditoria_id_seq', 48, true);


--
-- Name: gastos_excepciones_id_seq; Type: SEQUENCE SET; Schema: public; Owner: laravel_user
--

SELECT pg_catalog.setval('public.gastos_excepciones_id_seq', 2, true);


--
-- Name: gastos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: laravel_user
--

SELECT pg_catalog.setval('public.gastos_id_seq', 2, true);


--
-- Name: jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: laravel_user
--

SELECT pg_catalog.setval('public.jobs_id_seq', 6, true);


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: laravel_user
--

SELECT pg_catalog.setval('public.migrations_id_seq', 35, true);


--
-- Name: permissions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: laravel_user
--

SELECT pg_catalog.setval('public.permissions_id_seq', 53, true);


--
-- Name: politicas_gastos_auditoria_id_seq; Type: SEQUENCE SET; Schema: public; Owner: laravel_user
--

SELECT pg_catalog.setval('public.politicas_gastos_auditoria_id_seq', 3, true);


--
-- Name: politicas_gastos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: laravel_user
--

SELECT pg_catalog.setval('public.politicas_gastos_id_seq', 3, true);


--
-- Name: politicas_gastos_versiones_id_seq; Type: SEQUENCE SET; Schema: public; Owner: laravel_user
--

SELECT pg_catalog.setval('public.politicas_gastos_versiones_id_seq', 3, true);


--
-- Name: proyectos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: laravel_user
--

SELECT pg_catalog.setval('public.proyectos_id_seq', 2, true);


--
-- Name: roles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: laravel_user
--

SELECT pg_catalog.setval('public.roles_id_seq', 4, true);


--
-- Name: solicitud_aprobaciones_id_seq; Type: SEQUENCE SET; Schema: public; Owner: laravel_user
--

SELECT pg_catalog.setval('public.solicitud_aprobaciones_id_seq', 4, true);


--
-- Name: solicitud_detalles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: laravel_user
--

SELECT pg_catalog.setval('public.solicitud_detalles_id_seq', 10, true);


--
-- Name: solicitudes_auditoria_id_seq; Type: SEQUENCE SET; Schema: public; Owner: laravel_user
--

SELECT pg_catalog.setval('public.solicitudes_auditoria_id_seq', 7, true);


--
-- Name: solicitudes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: laravel_user
--

SELECT pg_catalog.setval('public.solicitudes_id_seq', 2, true);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: laravel_user
--

SELECT pg_catalog.setval('public.users_id_seq', 4, true);


--
-- Name: areas areas_codigo_unique; Type: CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.areas
    ADD CONSTRAINT areas_codigo_unique UNIQUE (codigo);


--
-- Name: areas areas_nombre_unique; Type: CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.areas
    ADD CONSTRAINT areas_nombre_unique UNIQUE (nombre);


--
-- Name: areas areas_pkey; Type: CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.areas
    ADD CONSTRAINT areas_pkey PRIMARY KEY (id);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: centros_costos centros_costos_codigo_unique; Type: CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.centros_costos
    ADD CONSTRAINT centros_costos_codigo_unique UNIQUE (codigo);


--
-- Name: centros_costos centros_costos_pkey; Type: CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.centros_costos
    ADD CONSTRAINT centros_costos_pkey PRIMARY KEY (id);


--
-- Name: concepto_rol concepto_rol_pkey; Type: CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.concepto_rol
    ADD CONSTRAINT concepto_rol_pkey PRIMARY KEY (concepto_id, rol_id);


--
-- Name: conceptos conceptos_codigo_unique; Type: CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.conceptos
    ADD CONSTRAINT conceptos_codigo_unique UNIQUE (codigo);


--
-- Name: conceptos conceptos_pkey; Type: CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.conceptos
    ADD CONSTRAINT conceptos_pkey PRIMARY KEY (id);


--
-- Name: empleados empleados_numero_nomina_unique; Type: CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.empleados
    ADD CONSTRAINT empleados_numero_nomina_unique UNIQUE (numero_nomina);


--
-- Name: empleados empleados_pkey; Type: CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.empleados
    ADD CONSTRAINT empleados_pkey PRIMARY KEY (id);


--
-- Name: empleados empleados_user_id_unique; Type: CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.empleados
    ADD CONSTRAINT empleados_user_id_unique UNIQUE (user_id);


--
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- Name: flujos_aprobacion flujos_aprobacion_pkey; Type: CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.flujos_aprobacion
    ADD CONSTRAINT flujos_aprobacion_pkey PRIMARY KEY (id);


--
-- Name: folio_counters folio_counters_pkey; Type: CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.folio_counters
    ADD CONSTRAINT folio_counters_pkey PRIMARY KEY (id);


--
-- Name: folio_counters folio_counters_prefix_year_unique; Type: CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.folio_counters
    ADD CONSTRAINT folio_counters_prefix_year_unique UNIQUE (prefix, year);


--
-- Name: gasto_comprobantes gasto_comprobantes_pkey; Type: CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.gasto_comprobantes
    ADD CONSTRAINT gasto_comprobantes_pkey PRIMARY KEY (id);


--
-- Name: gastos_auditoria gastos_auditoria_pkey; Type: CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.gastos_auditoria
    ADD CONSTRAINT gastos_auditoria_pkey PRIMARY KEY (id);


--
-- Name: gastos_excepciones gastos_excepciones_pkey; Type: CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.gastos_excepciones
    ADD CONSTRAINT gastos_excepciones_pkey PRIMARY KEY (id);


--
-- Name: gastos gastos_pkey; Type: CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.gastos
    ADD CONSTRAINT gastos_pkey PRIMARY KEY (id);


--
-- Name: job_batches job_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.job_batches
    ADD CONSTRAINT job_batches_pkey PRIMARY KEY (id);


--
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: model_has_permissions model_has_permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.model_has_permissions
    ADD CONSTRAINT model_has_permissions_pkey PRIMARY KEY (permission_id, model_id, model_type);


--
-- Name: model_has_roles model_has_roles_pkey; Type: CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.model_has_roles
    ADD CONSTRAINT model_has_roles_pkey PRIMARY KEY (role_id, model_id, model_type);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- Name: permissions permissions_name_guard_name_unique; Type: CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_name_guard_name_unique UNIQUE (name, guard_name);


--
-- Name: permissions permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_pkey PRIMARY KEY (id);


--
-- Name: politicas_gastos_auditoria politicas_gastos_auditoria_pkey; Type: CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.politicas_gastos_auditoria
    ADD CONSTRAINT politicas_gastos_auditoria_pkey PRIMARY KEY (id);


--
-- Name: politicas_gastos politicas_gastos_pkey; Type: CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.politicas_gastos
    ADD CONSTRAINT politicas_gastos_pkey PRIMARY KEY (id);


--
-- Name: politicas_gastos politicas_gastos_role_id_concepto_id_tipo_limite_vigencia_desde; Type: CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.politicas_gastos
    ADD CONSTRAINT politicas_gastos_role_id_concepto_id_tipo_limite_vigencia_desde UNIQUE (role_id, concepto_id, tipo_limite, vigencia_desde);


--
-- Name: politicas_gastos_versiones politicas_gastos_versiones_pkey; Type: CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.politicas_gastos_versiones
    ADD CONSTRAINT politicas_gastos_versiones_pkey PRIMARY KEY (id);


--
-- Name: proyectos proyectos_codigo_unique; Type: CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.proyectos
    ADD CONSTRAINT proyectos_codigo_unique UNIQUE (codigo);


--
-- Name: proyectos proyectos_pkey; Type: CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.proyectos
    ADD CONSTRAINT proyectos_pkey PRIMARY KEY (id);


--
-- Name: role_has_permissions role_has_permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.role_has_permissions
    ADD CONSTRAINT role_has_permissions_pkey PRIMARY KEY (permission_id, role_id);


--
-- Name: roles roles_name_guard_name_unique; Type: CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_name_guard_name_unique UNIQUE (name, guard_name);


--
-- Name: roles roles_pkey; Type: CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: solicitud_aprobaciones solicitud_aprobaciones_pkey; Type: CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.solicitud_aprobaciones
    ADD CONSTRAINT solicitud_aprobaciones_pkey PRIMARY KEY (id);


--
-- Name: solicitud_aprobaciones solicitud_aprobaciones_solicitud_id_role_id_unique; Type: CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.solicitud_aprobaciones
    ADD CONSTRAINT solicitud_aprobaciones_solicitud_id_role_id_unique UNIQUE (solicitud_id, role_id);


--
-- Name: solicitud_detalles solicitud_detalles_pkey; Type: CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.solicitud_detalles
    ADD CONSTRAINT solicitud_detalles_pkey PRIMARY KEY (id);


--
-- Name: solicitudes_auditoria solicitudes_auditoria_pkey; Type: CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.solicitudes_auditoria
    ADD CONSTRAINT solicitudes_auditoria_pkey PRIMARY KEY (id);


--
-- Name: solicitudes solicitudes_folio_unique; Type: CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.solicitudes
    ADD CONSTRAINT solicitudes_folio_unique UNIQUE (folio);


--
-- Name: solicitudes solicitudes_pkey; Type: CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.solicitudes
    ADD CONSTRAINT solicitudes_pkey PRIMARY KEY (id);


--
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: cache_expiration_index; Type: INDEX; Schema: public; Owner: laravel_user
--

CREATE INDEX cache_expiration_index ON public.cache USING btree (expiration);


--
-- Name: cache_locks_expiration_index; Type: INDEX; Schema: public; Owner: laravel_user
--

CREATE INDEX cache_locks_expiration_index ON public.cache_locks USING btree (expiration);


--
-- Name: conceptos_estatus_index; Type: INDEX; Schema: public; Owner: laravel_user
--

CREATE INDEX conceptos_estatus_index ON public.conceptos USING btree (estatus);


--
-- Name: conceptos_tipo_aplicacion_index; Type: INDEX; Schema: public; Owner: laravel_user
--

CREATE INDEX conceptos_tipo_aplicacion_index ON public.conceptos USING btree (tipo_aplicacion);


--
-- Name: empleados_estatus_index; Type: INDEX; Schema: public; Owner: laravel_user
--

CREATE INDEX empleados_estatus_index ON public.empleados USING btree (estatus);


--
-- Name: empleados_rfc_index; Type: INDEX; Schema: public; Owner: laravel_user
--

CREATE INDEX empleados_rfc_index ON public.empleados USING btree (rfc);


--
-- Name: gasto_comprobantes_sat_status_index; Type: INDEX; Schema: public; Owner: laravel_user
--

CREATE INDEX gasto_comprobantes_sat_status_index ON public.gasto_comprobantes USING btree (sat_status);


--
-- Name: gastos_auditoria_actor_id_index; Type: INDEX; Schema: public; Owner: laravel_user
--

CREATE INDEX gastos_auditoria_actor_id_index ON public.gastos_auditoria USING btree (actor_id);


--
-- Name: gastos_auditoria_evento_index; Type: INDEX; Schema: public; Owner: laravel_user
--

CREATE INDEX gastos_auditoria_evento_index ON public.gastos_auditoria USING btree (evento);


--
-- Name: gastos_auditoria_excepcion_id_index; Type: INDEX; Schema: public; Owner: laravel_user
--

CREATE INDEX gastos_auditoria_excepcion_id_index ON public.gastos_auditoria USING btree (excepcion_id);


--
-- Name: gastos_auditoria_gasto_id_index; Type: INDEX; Schema: public; Owner: laravel_user
--

CREATE INDEX gastos_auditoria_gasto_id_index ON public.gastos_auditoria USING btree (gasto_id);


--
-- Name: gastos_estatus_index; Type: INDEX; Schema: public; Owner: laravel_user
--

CREATE INDEX gastos_estatus_index ON public.gastos USING btree (estatus);


--
-- Name: gastos_fecha_gasto_index; Type: INDEX; Schema: public; Owner: laravel_user
--

CREATE INDEX gastos_fecha_gasto_index ON public.gastos USING btree (fecha_gasto);


--
-- Name: gastos_solicitud_id_concepto_id_fecha_gasto_index; Type: INDEX; Schema: public; Owner: laravel_user
--

CREATE INDEX gastos_solicitud_id_concepto_id_fecha_gasto_index ON public.gastos USING btree (solicitud_id, concepto_id, fecha_gasto);


--
-- Name: gastos_uuid_factura_index; Type: INDEX; Schema: public; Owner: laravel_user
--

CREATE INDEX gastos_uuid_factura_index ON public.gastos USING btree (uuid_factura);


--
-- Name: jobs_queue_index; Type: INDEX; Schema: public; Owner: laravel_user
--

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


--
-- Name: model_has_permissions_model_id_model_type_index; Type: INDEX; Schema: public; Owner: laravel_user
--

CREATE INDEX model_has_permissions_model_id_model_type_index ON public.model_has_permissions USING btree (model_id, model_type);


--
-- Name: model_has_roles_model_id_model_type_index; Type: INDEX; Schema: public; Owner: laravel_user
--

CREATE INDEX model_has_roles_model_id_model_type_index ON public.model_has_roles USING btree (model_id, model_type);


--
-- Name: politicas_gastos_auditoria_actor_id_index; Type: INDEX; Schema: public; Owner: laravel_user
--

CREATE INDEX politicas_gastos_auditoria_actor_id_index ON public.politicas_gastos_auditoria USING btree (actor_id);


--
-- Name: politicas_gastos_auditoria_created_at_index; Type: INDEX; Schema: public; Owner: laravel_user
--

CREATE INDEX politicas_gastos_auditoria_created_at_index ON public.politicas_gastos_auditoria USING btree (created_at);


--
-- Name: politicas_gastos_auditoria_evento_index; Type: INDEX; Schema: public; Owner: laravel_user
--

CREATE INDEX politicas_gastos_auditoria_evento_index ON public.politicas_gastos_auditoria USING btree (evento);


--
-- Name: politicas_gastos_auditoria_politica_id_index; Type: INDEX; Schema: public; Owner: laravel_user
--

CREATE INDEX politicas_gastos_auditoria_politica_id_index ON public.politicas_gastos_auditoria USING btree (politica_id);


--
-- Name: politicas_gastos_estatus_index; Type: INDEX; Schema: public; Owner: laravel_user
--

CREATE INDEX politicas_gastos_estatus_index ON public.politicas_gastos USING btree (estatus);


--
-- Name: politicas_gastos_role_id_concepto_id_index; Type: INDEX; Schema: public; Owner: laravel_user
--

CREATE INDEX politicas_gastos_role_id_concepto_id_index ON public.politicas_gastos USING btree (role_id, concepto_id);


--
-- Name: politicas_gastos_versiones_politica_id_estatus_index; Type: INDEX; Schema: public; Owner: laravel_user
--

CREATE INDEX politicas_gastos_versiones_politica_id_estatus_index ON public.politicas_gastos_versiones USING btree (politica_id, estatus);


--
-- Name: politicas_gastos_versiones_role_id_concepto_id_estatus_index; Type: INDEX; Schema: public; Owner: laravel_user
--

CREATE INDEX politicas_gastos_versiones_role_id_concepto_id_estatus_index ON public.politicas_gastos_versiones USING btree (role_id, concepto_id, estatus);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: laravel_user
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: laravel_user
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- Name: solicitud_detalles_solicitud_id_concepto_id_index; Type: INDEX; Schema: public; Owner: laravel_user
--

CREATE INDEX solicitud_detalles_solicitud_id_concepto_id_index ON public.solicitud_detalles USING btree (solicitud_id, concepto_id);


--
-- Name: solicitudes_estatus_index; Type: INDEX; Schema: public; Owner: laravel_user
--

CREATE INDEX solicitudes_estatus_index ON public.solicitudes USING btree (estatus);


--
-- Name: concepto_rol concepto_rol_concepto_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.concepto_rol
    ADD CONSTRAINT concepto_rol_concepto_id_foreign FOREIGN KEY (concepto_id) REFERENCES public.conceptos(id) ON DELETE CASCADE;


--
-- Name: concepto_rol concepto_rol_rol_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.concepto_rol
    ADD CONSTRAINT concepto_rol_rol_id_foreign FOREIGN KEY (rol_id) REFERENCES public.roles(id) ON DELETE CASCADE;


--
-- Name: empleados empleados_area_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.empleados
    ADD CONSTRAINT empleados_area_id_foreign FOREIGN KEY (area_id) REFERENCES public.areas(id) ON DELETE SET NULL;


--
-- Name: empleados empleados_centro_costo_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.empleados
    ADD CONSTRAINT empleados_centro_costo_id_foreign FOREIGN KEY (centro_costo_id) REFERENCES public.centros_costos(id) ON DELETE SET NULL;


--
-- Name: empleados empleados_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.empleados
    ADD CONSTRAINT empleados_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: flujos_aprobacion flujos_aprobacion_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.flujos_aprobacion
    ADD CONSTRAINT flujos_aprobacion_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(id);


--
-- Name: gasto_comprobantes gasto_comprobantes_gasto_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.gasto_comprobantes
    ADD CONSTRAINT gasto_comprobantes_gasto_id_foreign FOREIGN KEY (gasto_id) REFERENCES public.gastos(id) ON DELETE CASCADE;


--
-- Name: gasto_comprobantes gasto_comprobantes_subido_por_foreign; Type: FK CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.gasto_comprobantes
    ADD CONSTRAINT gasto_comprobantes_subido_por_foreign FOREIGN KEY (subido_por) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: gasto_comprobantes gasto_comprobantes_validado_por_foreign; Type: FK CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.gasto_comprobantes
    ADD CONSTRAINT gasto_comprobantes_validado_por_foreign FOREIGN KEY (validado_por) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: gastos_auditoria gastos_auditoria_actor_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.gastos_auditoria
    ADD CONSTRAINT gastos_auditoria_actor_id_foreign FOREIGN KEY (actor_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: gastos_auditoria gastos_auditoria_excepcion_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.gastos_auditoria
    ADD CONSTRAINT gastos_auditoria_excepcion_id_foreign FOREIGN KEY (excepcion_id) REFERENCES public.gastos_excepciones(id) ON DELETE SET NULL;


--
-- Name: gastos_auditoria gastos_auditoria_gasto_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.gastos_auditoria
    ADD CONSTRAINT gastos_auditoria_gasto_id_foreign FOREIGN KEY (gasto_id) REFERENCES public.gastos(id) ON DELETE SET NULL;


--
-- Name: gastos gastos_concepto_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.gastos
    ADD CONSTRAINT gastos_concepto_id_foreign FOREIGN KEY (concepto_id) REFERENCES public.conceptos(id);


--
-- Name: gastos_excepciones gastos_excepciones_aprobado_por_foreign; Type: FK CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.gastos_excepciones
    ADD CONSTRAINT gastos_excepciones_aprobado_por_foreign FOREIGN KEY (aprobado_por) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: gastos_excepciones gastos_excepciones_gasto_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.gastos_excepciones
    ADD CONSTRAINT gastos_excepciones_gasto_id_foreign FOREIGN KEY (gasto_id) REFERENCES public.gastos(id) ON DELETE CASCADE;


--
-- Name: gastos gastos_solicitud_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.gastos
    ADD CONSTRAINT gastos_solicitud_id_foreign FOREIGN KEY (solicitud_id) REFERENCES public.solicitudes(id) ON DELETE CASCADE;


--
-- Name: model_has_permissions model_has_permissions_permission_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.model_has_permissions
    ADD CONSTRAINT model_has_permissions_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES public.permissions(id) ON DELETE CASCADE;


--
-- Name: model_has_roles model_has_roles_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.model_has_roles
    ADD CONSTRAINT model_has_roles_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE CASCADE;


--
-- Name: politicas_gastos_auditoria politicas_gastos_auditoria_actor_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.politicas_gastos_auditoria
    ADD CONSTRAINT politicas_gastos_auditoria_actor_id_foreign FOREIGN KEY (actor_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: politicas_gastos_auditoria politicas_gastos_auditoria_politica_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.politicas_gastos_auditoria
    ADD CONSTRAINT politicas_gastos_auditoria_politica_id_foreign FOREIGN KEY (politica_id) REFERENCES public.politicas_gastos(id) ON DELETE SET NULL;


--
-- Name: politicas_gastos_auditoria politicas_gastos_auditoria_version_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.politicas_gastos_auditoria
    ADD CONSTRAINT politicas_gastos_auditoria_version_id_foreign FOREIGN KEY (version_id) REFERENCES public.politicas_gastos_versiones(id) ON DELETE SET NULL;


--
-- Name: politicas_gastos politicas_gastos_concepto_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.politicas_gastos
    ADD CONSTRAINT politicas_gastos_concepto_id_foreign FOREIGN KEY (concepto_id) REFERENCES public.conceptos(id);


--
-- Name: politicas_gastos politicas_gastos_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.politicas_gastos
    ADD CONSTRAINT politicas_gastos_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(id);


--
-- Name: politicas_gastos_versiones politicas_gastos_versiones_aprobado_por_foreign; Type: FK CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.politicas_gastos_versiones
    ADD CONSTRAINT politicas_gastos_versiones_aprobado_por_foreign FOREIGN KEY (aprobado_por) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: politicas_gastos_versiones politicas_gastos_versiones_concepto_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.politicas_gastos_versiones
    ADD CONSTRAINT politicas_gastos_versiones_concepto_id_foreign FOREIGN KEY (concepto_id) REFERENCES public.conceptos(id);


--
-- Name: politicas_gastos_versiones politicas_gastos_versiones_creado_por_foreign; Type: FK CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.politicas_gastos_versiones
    ADD CONSTRAINT politicas_gastos_versiones_creado_por_foreign FOREIGN KEY (creado_por) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: politicas_gastos_versiones politicas_gastos_versiones_politica_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.politicas_gastos_versiones
    ADD CONSTRAINT politicas_gastos_versiones_politica_id_foreign FOREIGN KEY (politica_id) REFERENCES public.politicas_gastos(id) ON DELETE CASCADE;


--
-- Name: politicas_gastos_versiones politicas_gastos_versiones_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.politicas_gastos_versiones
    ADD CONSTRAINT politicas_gastos_versiones_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(id);


--
-- Name: proyectos proyectos_centro_costo_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.proyectos
    ADD CONSTRAINT proyectos_centro_costo_id_foreign FOREIGN KEY (centro_costo_id) REFERENCES public.centros_costos(id);


--
-- Name: proyectos proyectos_responsable_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.proyectos
    ADD CONSTRAINT proyectos_responsable_id_foreign FOREIGN KEY (responsable_id) REFERENCES public.empleados(id) ON DELETE SET NULL;


--
-- Name: role_has_permissions role_has_permissions_permission_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.role_has_permissions
    ADD CONSTRAINT role_has_permissions_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES public.permissions(id) ON DELETE CASCADE;


--
-- Name: role_has_permissions role_has_permissions_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.role_has_permissions
    ADD CONSTRAINT role_has_permissions_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE CASCADE;


--
-- Name: solicitud_aprobaciones solicitud_aprobaciones_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.solicitud_aprobaciones
    ADD CONSTRAINT solicitud_aprobaciones_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(id);


--
-- Name: solicitud_aprobaciones solicitud_aprobaciones_solicitud_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.solicitud_aprobaciones
    ADD CONSTRAINT solicitud_aprobaciones_solicitud_id_foreign FOREIGN KEY (solicitud_id) REFERENCES public.solicitudes(id) ON DELETE CASCADE;


--
-- Name: solicitud_aprobaciones solicitud_aprobaciones_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.solicitud_aprobaciones
    ADD CONSTRAINT solicitud_aprobaciones_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id);


--
-- Name: solicitud_detalles solicitud_detalles_concepto_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.solicitud_detalles
    ADD CONSTRAINT solicitud_detalles_concepto_id_foreign FOREIGN KEY (concepto_id) REFERENCES public.conceptos(id);


--
-- Name: solicitud_detalles solicitud_detalles_solicitud_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.solicitud_detalles
    ADD CONSTRAINT solicitud_detalles_solicitud_id_foreign FOREIGN KEY (solicitud_id) REFERENCES public.solicitudes(id) ON DELETE CASCADE;


--
-- Name: solicitudes solicitudes_area_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.solicitudes
    ADD CONSTRAINT solicitudes_area_id_foreign FOREIGN KEY (area_id) REFERENCES public.areas(id);


--
-- Name: solicitudes_auditoria solicitudes_auditoria_actor_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.solicitudes_auditoria
    ADD CONSTRAINT solicitudes_auditoria_actor_id_foreign FOREIGN KEY (actor_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: solicitudes_auditoria solicitudes_auditoria_solicitud_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.solicitudes_auditoria
    ADD CONSTRAINT solicitudes_auditoria_solicitud_id_foreign FOREIGN KEY (solicitud_id) REFERENCES public.solicitudes(id) ON DELETE CASCADE;


--
-- Name: solicitudes solicitudes_empleado_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.solicitudes
    ADD CONSTRAINT solicitudes_empleado_id_foreign FOREIGN KEY (empleado_id) REFERENCES public.empleados(id);


--
-- Name: solicitudes solicitudes_proyecto_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: laravel_user
--

ALTER TABLE ONLY public.solicitudes
    ADD CONSTRAINT solicitudes_proyecto_id_foreign FOREIGN KEY (proyecto_id) REFERENCES public.proyectos(id);


--
-- PostgreSQL database dump complete
--

\unrestrict VYMcMt64lnfBswVFo5gEttghPd9fX5iNUmL1mKHFJmWiFX4ctIR7UZ5t1Z8Jmnq

