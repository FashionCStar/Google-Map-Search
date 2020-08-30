import { Component, OnInit } from '@angular/core';
import {HttpClient} from "@angular/common/http";
import {FormBuilder} from "@angular/forms";
import {ProductService} from "../../services/product.service";

@Component({
  selector: 'app-gallery-view',
  templateUrl: './gallery-view.component.html',
  styleUrls: ['./gallery-view.component.scss']
})
export class GalleryViewComponent implements OnInit {

  datas: any[] = [];
  constructor(private http: HttpClient, private _formBuilder: FormBuilder, private _productService: ProductService) { }

  ngOnInit() {
    this.getPropertyList();
  }

  getPropertyList(): void {
    this._productService.getPropertyList(null).subscribe(res => {
      if (res['success']) {
        this.datas = res['data'];
      }
    });
  }
}
