{**
 * Copyright since 2021 InPost S.A.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the EUPL-1.2 or later.
 * You may not use this work except in compliance with the Licence.
 *
 * You may obtain a copy of the Licence at:
 * https://joinup.ec.europa.eu/software/page/eupl
 * It is also bundled with this package in the file LICENSE.txt
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the Licence is distributed on an AS IS basis,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the Licence for the specific language governing permissions
 * and limitations under the Licence.
 *
 * @author    InPost S.A.
 * @copyright Since 2021 InPost S.A.
 * @license   https://joinup.ec.europa.eu/software/page/eupl
 *}
{if $parcel.template}
  <div class="form-group row">
    <label class="form-control-label font-weight-bold">{l s='Dimension template' mod='inpostshipping'}</label>
    <div class="col-sm">
      <div class="form-control border-0">{$parcel.template|escape:'html':'UTF-8'}</div>
    </div>
  </div>
{else}
  <div class="form-group row">
    <label class="form-control-label font-weight-bold">{l s='Dimensions' mod='inpostshipping'}</label>
    <div class="col-sm">
      <div class="form-control border-0">
        {$parcel.dimensions.length|floatval} x {$parcel.dimensions.width|floatval} x {$parcel.dimensions.height|floatval} mm
      </div>
    </div>
  </div>

  <div class="form-group row">
    <label class="form-control-label font-weight-bold">{l s='Weight' mod='inpostshipping'}</label>
    <div class="col-sm">
      <div class="form-control border-0">{$parcel.dimensions.weight|floatval} kg</div>
    </div>
  </div>
{/if}

{if $parcel.is_non_standard}
  <div class="form-group row">
    <label class="form-control-label font-weight-bold">{l s='Not standard' mod='inpostshipping'}</label>
    <div class="col-sm">
      <div class="form-control border-0">{l s='Yes' mod='inpostshipping'}</div>
    </div>
  </div>
{/if}
