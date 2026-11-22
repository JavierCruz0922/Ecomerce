"""Export the Productos worksheet as a reproducible seeder fixture."""

import argparse
import json
import posixpath
import re
import zipfile
from pathlib import Path
from xml.etree import ElementTree


HEADERS = {
    "A": ("Código", "sku"),
    "B": ("Producto", "name"),
    "C": ("Categoría", "category"),
    "D": ("Color", "color"),
    "E": ("Material", "material"),
    "F": ("Descripción", "description"),
    "G": ("Precio unitario (COP)", "price_cop"),
    "H": ("Existencias", "stock"),
    "I": ("Archivo de imagen", "image_path"),
}
EXPECTED_SKUS = {f"PAP{index:03}" for index in range(1, 21)}


def read_products(source):
    with zipfile.ZipFile(source) as workbook:
        sheets = ElementTree.fromstring(workbook.read("xl/workbook.xml"))
        relations = ElementTree.fromstring(workbook.read("xl/_rels/workbook.xml.rels"))
        targets = {relation.get("Id"): relation.get("Target") for relation in relations}
        product_sheet = next(
            (sheet for sheet in sheets.findall(".//{*}sheet") if sheet.get("name") == "Productos"),
            None,
        )
        if product_sheet is None:
            raise ValueError("Falta la hoja Productos.")
        relation_id = next(value for key, value in product_sheet.attrib.items() if key.endswith("}id"))
        target = targets[relation_id]
        sheet_path = target.lstrip("/") if target.startswith("/") else posixpath.normpath("xl/" + target)

        shared_strings = []
        if "xl/sharedStrings.xml" in workbook.namelist():
            shared_strings = [
                "".join(text.text or "" for text in entry.findall(".//{*}t"))
                for entry in ElementTree.fromstring(workbook.read("xl/sharedStrings.xml"))
            ]

        def cell_value(cell):
            value = cell.findtext("{*}v", "")
            if cell.get("t") == "s":
                return shared_strings[int(value)].strip()
            if cell.get("t") == "inlineStr":
                return "".join(text.text or "" for text in cell.findall(".//{*}t")).strip()
            return value.strip()

        products = {}
        headers_found = False
        for row in ElementTree.fromstring(workbook.read(sheet_path)).findall(".//{*}row"):
            cells = {
                re.match(r"[A-Z]+", cell.get("r"))[0]: cell_value(cell)
                for cell in row.findall("{*}c")
            }
            row_number = int(row.get("r"))
            if row_number == 4:
                headers_found = all(cells.get(column) == label for column, (label, _) in HEADERS.items())
            if row_number <= 4 or not any(cells.values()):
                continue

            item = {field: cells.get(column, "") for column, (_, field) in HEADERS.items()}
            sku = item["sku"]
            if not all(item.values()) or sku not in EXPECTED_SKUS or sku in products:
                raise ValueError(f"Fila {row_number} incompleta, duplicada o con SKU inválido.")
            for field in ("price_cop", "stock"):
                if not re.fullmatch(r"[0-9]+", item[field]):
                    raise ValueError(f"Fila {row_number}: {field} debe ser un entero.")
                item[field] = int(item[field])
            if item["price_cop"] <= 0 or item["stock"] > 2147483647:
                raise ValueError(f"Fila {row_number}: precio o existencias fuera de rango.")
            if not re.fullmatch(rf"{sku}\.(?:png|jpe?g)", item["image_path"], re.IGNORECASE):
                raise ValueError(f"Fila {row_number}: referencia de imagen inválida.")
            products[sku] = item

    if not headers_found or products.keys() != EXPECTED_SKUS:
        raise ValueError("La hoja Productos debe conservar su encabezado y los 20 SKU PAP001–PAP020.")
    return [products[sku] for sku in sorted(products)]


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("source", type=Path)
    parser.add_argument("destination", type=Path)
    parser.add_argument("--check", action="store_true", help="Compare the existing fixture without writing.")
    args = parser.parse_args()
    try:
        if args.source.resolve() == args.destination.resolve():
            raise ValueError("El destino debe ser distinto del Excel de origen.")
        expected = (json.dumps(read_products(args.source), ensure_ascii=False, indent=2) + "\n").encode("utf-8")
        if args.check:
            if args.destination.read_bytes() != expected:
                raise ValueError("El JSON difiere de la hoja Productos.")
        else:
            args.destination.parent.mkdir(parents=True, exist_ok=True)
            args.destination.write_bytes(expected)
    except (OSError, ValueError, KeyError, IndexError, StopIteration, zipfile.BadZipFile, ElementTree.ParseError) as error:
        parser.exit(1, f"{error}\n")
    print("20 productos verificados (PAP001-PAP020).")


if __name__ == "__main__":
    main()
